<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block gamification ladder table.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\output;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

use context_helper;
use renderer_base;
use stdClass;
use table_sql;
use user_picture;
use block_gamification\local\course_world;
use block_gamification\local\config\course_world_config;
use block_gamification\local\gamification\course_user_state_store;


/**
 * Block gamification ladder table class.
 *
 * This implementation is terrible and highly depends on the internals of
 * course_user_state_store. We cannot switch to another store and hope this
 * ladder table will work as egamificationected.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ladder_table extends table_sql {

    /** @var string The key of the user ID column. It's "id" because we get the user from the state. */
    public $useridfield = 'id';

    /** @var course_world Course world. */
    protected $world = null;

    /** @var course_user_state_store The store. */
    protected $store = null;

    /** @var block_gamification_renderer gamification Renderer. */
    protected $gamificationoutput = null;

    /** @var int The user ID we're viewing the ladder for. */
    protected $userid;

    /** @var Cache of the user record, use {@link self::get_user_record()}. */
    protected $currentuserrecord;

    /** @var array Contains the names of the additional columns. */
    protected $additionalcols = [];

    /** @var int The identity mode. */
    protected $identitymode = course_world_config::IDENTITY_ON;

    /** @var boolean Only show neighbours. */
    protected $neighboursonly = false;

    /** @var boolean When showing neighbours only show n before. */
    protected $neighboursabove = 3;

    /** @var boolean When showing neighbours only show n after. */
    protected $neighboursbelow = 3;

    /** @var int The rank mode. */
    protected $rankmode = course_world_config::RANK_ON;

    /** @var int The level we're starting from to compute the rank. */
    protected $startinglevel;

    /** @var int The offset to start with to compute the rank. */
    protected $startingoffset;

    /** @var int The rank to start with to compute the rank. */
    protected $startingrank;

    /** @var int The gamification we're counting from to compute the rank. */
    protected $startinggamification;

    /** @var int The gamification to compare with. Used with RANK_REL. */
    protected $startinggamificationdiff;

    /**
     * Constructor.
     *
     * @param course_world $world The world.
     * @param renderer_base $renderer The renderer.
     * @param course_user_state_store $store The store.
     * @param int $groupid The group ID.
     * @param array $options Options.
     * @param int $userid The user viewing this.
     */
    public function __construct(
            course_world $world,
            renderer_base $renderer,
            course_user_state_store $store,
            $groupid,
            array $options = [],
            $userid = null
        ) {

        global $CFG, $USER;
        parent::__construct('block_gamification_ladder');

        if (isset($options['rankmode'])) {
            $this->rankmode = $options['rankmode'];
        }
        if (isset($options['neighboursonly'])) {
            $this->neighboursonly = $options['neighboursonly'];
        }
        if (isset($options['neighboursabove'])) {
            $this->neighboursabove = $options['neighboursabove'];
        }
        if (isset($options['neighboursbelow'])) {
            $this->neighboursbelow = $options['neighboursbelow'];
        }
        if (isset($options['identitymode'])) {
            $this->identitymode = $options['identitymode'];
        }
        if (isset($options['additionalcols'])) {
            $this->additionalcols = $options['additionalcols'];
        }

        // The user ID we're viewing the ladder for.
        if ($userid === null) {
            $userid = $USER->id;
        }
        $this->userid = $userid;

        // Block gamification stuff.
        $this->world = $world;
        $this->store = $store;
        $this->gamificationoutput = $renderer;
        $courseid = $this->world->get_courseid();

        // Define columns, and headers.
        $columns = [];
        $headers = [];
        if ($this->rankmode != course_world_config::RANK_OFF) {
            if ($this->rankmode == course_world_config::RANK_REL) {
                $columns[] = 'lvl';
                $headers[] = get_string('level', 'block_gamification');
                $columns[] = 'rank';
                $headers[] = get_string('difference', 'block_gamification');
            } else {
                $columns[] = 'rank';
                $headers[] = get_string('rank', 'block_gamification');
                $columns[] = 'lvl';
                $headers[] = get_string('level', 'block_gamification');
            }
        } else {
            $columns[] = 'lvl';
            $headers[] = get_string('level', 'block_gamification');
        }

        $columns[] = 'fullname';
        $headers[] = get_string('participant', 'block_gamification');;

        // Additional columns.
        if (in_array('gamification', $this->additionalcols)) {
            $columns[] = 'gamification';
            $headers[] = get_string('total', 'block_gamification');
        }
        if (in_array('progress', $this->additionalcols)) {
            $columns[] = 'progress';
            $headers[] = get_string('progress', 'block_gamification');
        }

        $this->define_columns($columns);
        $this->define_headers($headers);

        // Define SQL.
        $sqlfrom = '';
        $sqlparams = [];
        if ($groupid) {
            $sqlfrom = '{block_gamification} x
                     JOIN {groups_members} gm
                       ON gm.groupid = :groupid
                      AND gm.userid = x.userid
                     JOIN {user} u
                       ON x.userid = u.id';
            $sqlparams = array('groupid' => $groupid);
        } else {
            $sqlfrom = '{block_gamification} x JOIN {user} u ON x.userid = u.id';
        }
        $sqlfrom .= " JOIN {context} ctx
                        ON ctx.instanceid = u.id
                       AND ctx.contextlevel = :contextlevel";
        $sqlparams += array('contextlevel' => CONTEXT_USER);

        $this->sql = new stdClass();
        $this->sql->fields = 'x.*, ' .
            user_picture::fields('u', null, 'userid') . ', ' .
            context_helper::get_preload_record_columns_sql('ctx');
        $this->sql->from = $sqlfrom;
        $this->sql->where = 'courseid = :courseid';
        if(!is_siteadmin()){
            $this->sql->where .= " AND u.open_costcenterid=$USER->open_costcenterid ";
        }
        $this->sql->params = array_merge(array('courseid' => $courseid), $sqlparams);

        // Define various table settings.
        $this->sortable(false);
        $this->no_sorting('userpic');
        $this->no_sorting('progress');
        $this->collapsible(false);
        $this->set_attribute('class', 'block_gamification-table');
        $this->column_class('rank', 'col-rank');
        $this->column_class('lvl', 'col-lvl');
        $this->column_class('userpic', 'col-userpic');
    }

    /**
     * Process the data returned by the query.
     *
     * @see self::compute_rank_start()
     * @return void
     */
    public function build_table() {
        global $USER;

        $this->compute_rank_start();

        $rank = $this->startingrank;
        $lastlvl = $this->startinglevel;
        $lastgamification = $this->startinggamification;
        $offset = $this->startingoffset;
        $gamificationtodiff = $this->startinggamificationdiff;

        if ($this->rawdata) {
            foreach ($this->rawdata as $row) {

                // Add the state, and update the level in case it's incorrect. Though
                // if the level is incorrect there the ordering will be as well so it
                // better be right really.
                $row->state = $this->make_state_from_record($row);
                $row->lvl = $row->state->get_level()->get_level();

                if ($this->rankmode == course_world_config::RANK_ON) {
                    // Show the real rank.

                    // If this row is different than the previous one.
                    if ($row->lvl != $lastlvl || $row->gamification != $lastgamification) {
                        $rank += $offset;
                        $offset = 1;
                        $lastlvl = $row->lvl;
                        $lastgamification = $row->gamification;
                    } else {
                        $offset++;
                    }
                    $row->rank = $rank;

                } else if ($this->rankmode == course_world_config::RANK_REL) {
                    // Show a "relative" rank, the difference between a student and another.

                    // There was no indication of what gamification to diff with, let's take the first entry.
                    if ($gamificationtodiff == -1 && $lastgamification == -1) {
                        $gamificationtodiff = $row->gamification;
                    }

                    // The last row does not this one.
                    if ($row->gamification != $lastgamification) {
                        $rank = $row->gamification - $gamificationtodiff;
                        $lastgamification = $row->gamification;
                    }

                    $row->rank = $rank;
                }

                $classes = ($this->userid == $row->userid) ? 'highlight-row' : '';
                $formattedrow = $this->format_row($row);
                $this->add_data_keyed($formattedrow, $classes);
            }
        }
    }

    /**
     * Formats the column fullname.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    public function col_fullname($row) {
        $o = $this->col_userpic($row);
        if ($this->identitymode == course_world_config::IDENTITY_OFF && $row->userid != $this->userid) {
            $o .= get_string('someoneelse', 'block_gamification');
        } else {
            $o .= parent::col_fullname($row->state->get_user());
        }
        return $o;
    }

    /**
     * Formats the level.
     *
     * @param stdClass $row Table row.
     * @return string
     */
    public function col_lvl($row) {
        return $this->gamificationoutput->small_level_badge($row->state->get_level());
    }

    /**
     * Formats the column progress.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_progress($row) {
        return $this->gamificationoutput->progress_bar($row->state);
    }

    /**
     * Formats the rank column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_rank($row) {
        if ($this->rankmode == course_world_config::RANK_REL) {
            $symbol = '';
            if ($row->rank > 0) {
                $symbol = '+';
            }
            // We want + when it's positive, and - when it's negative, else nothing.
            return $symbol . $this->gamificationoutput->gamification($row->rank);
        }
        return $row->rank;
    }

    /**
     * Formats the rank column.
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_gamification($row) {
        return $this->gamificationoutput->gamification($row->gamification);
    }

    /**
     * Formats the column userpic.
     *
     * @param stdClass $row Table row.
     * @return string Output produced.
     */
    protected function col_userpic($row) {
        if ($this->identitymode == course_world_config::IDENTITY_OFF && $this->userid != $row->userid) {
            static $guestuser = null;
            if ($guestuser === null) {
                $guestuser = guest_user();
            }
            return $this->gamificationoutput->user_picture($guestuser, array('link' => false, 'alttext' => false));
        }

        return $this->gamificationoutput->user_picture($row->state->get_user());
    }

    /**
     * Guesses where to start the rank computation.
     *
     * @return void
     */
    protected function compute_rank_start() {
        global $DB;

        $this->startingrank = 0;
        $this->startinglevel = -1;
        $this->startinggamification = -1;
        $this->startingoffset = 1;
        $this->startinggamificationdiff = -1;

        if ($this->rankmode == course_world_config::RANK_ON && !empty($this->rawdata)) {
            // Guess the starting rank.
            $record = reset($this->rawdata);
            $sql = "SELECT COUNT(x.id)
                      FROM {$this->sql->from}
                     WHERE {$this->sql->where}
                       AND x.gamification > :neighgamification";
            $this->startingrank = $DB->count_records_sql($sql, $this->sql->params + array('neighgamification' => $record->gamification)) + 1;
            $params = $this->sql->params + array(
                'neighid' => $record->id,
                'neighgamification' => $record->gamification,
                'neighgamificationeq' => $record->gamification
            );
            $sql = "SELECT COUNT(x.id)
                      FROM {$this->sql->from}
                     WHERE {$this->sql->where}
                       AND (x.gamification > :neighgamification
                        OR (x.gamification = :neighgamificationeq AND x.id < :neighid))";
            $this->startingoffset = 1 + $DB->count_records_sql($sql, $params) - $this->startingrank;
            $this->startinglevel = $record->lvl;
            $this->startinggamification = $record->gamification;

        } else if ($this->rankmode == course_world_config::RANK_REL) {
            // When relative, set self gamification as difference.
            $record = $this->get_user_record($this->userid);
            if ($record) {
                $this->startinggamificationdiff = $record->gamification;
            }
        }
    }

    /**
     * Get the current user record.
     *
     * @return stdClass|false
     */
    protected function get_user_record() {
        global $DB, $USER;

        if ($this->currentuserrecord === null) {
            $sqlme = "SELECT {$this->sql->fields}
                        FROM {$this->sql->from}
                       WHERE {$this->sql->where}
                         AND x.userid = :myuserid";
            $record = $DB->get_record_sql($sqlme, $this->sql->params + array('myuserid' => $this->userid));

            // Hack so that admin can see something. Hopefully we won't create too many bugs in case of missing fields.
            if (empty($record) && $this->neighboursonly && $this->world->get_access_permissions()->can_manage()) {
                $record = (object) array(
                    'id' => 0,
                    'userid' => $this->userid,
                    'courseid' => $this->world->get_courseid(),
                    'gamification' => 0,
                    'lvl' => 1,
                );
                $record = username_load_fields_from_object($record, $USER);
                $record->picture = $USER->picture;
                $record->imagealt = $USER->imagealt;
                $record->email = $USER->email;
            }

            $this->currentuserrecord = $record;
        }

        return $this->currentuserrecord;
    }

    /**
     * Get the columns to sort by.
     *
     * @return array column => SORT_ constant.
     */
    public function get_sort_columns() {
        return ['x.lvl' => SORT_DESC, 'x.gamification' => SORT_DESC, 'x.id' => SORT_ASC];
    }

    /**
     * Make state from record.
     *
     * @param stdClass $row Table row.
     * @return user_state
     */
    protected function make_state_from_record($row) {
        return $this->store->make_state_from_record($row);
    }

    /**
     * Query the db.
     *
     * This is overridden to get all the records in order to generate
     * an accurate rank.
     *
     * @param int $pagesize Size of page for paginated displayed table.
     * @param bool $useinitialsbar Do you want to use the initials bar?
     */
    public function query_db($pagesize, $useinitialsbar=true) {
        global $DB;

        // Only display neighbours.
        if ($this->neighboursonly) {
            $this->query_db_neighbours($this->userid, $this->neighboursabove, $this->neighboursbelow);
            return;
        }

        // When we're not downloading there is a pagination.
        if (!$this->is_downloading()) {
            if ($this->countsql === null) {
                $this->countsql = 'SELECT COUNT(1) FROM '.$this->sql->from.' WHERE '.$this->sql->where;
                $this->countparams = $this->sql->params;
            }
            $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
            if ($useinitialsbar && !$this->is_downloading()) {
                $this->initialbars($grandtotal > $pagesize);
            }

            list($wsql, $wparams) = $this->get_sql_where();
            if ($wsql) {
                $this->countsql .= ' AND '.$wsql;
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND '.$wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $total  = $DB->count_records_sql($this->countsql, $this->countparams);
            } else {
                $total = $grandtotal;
            }

            $this->pagesize($pagesize, $total);

            // When we are displaying the full ranking, and the user did not request a specific page,
            // we will guess what page they appear on and jump right to that page. This logic makes
            // some assumption on the logic present in the parent class, not ideal but we have no choice.
            $requestedpage = optional_param($this->request[TABLE_VAR_PAGE], null, PARAM_INT);
            if ($requestedpage === null && ($record = $this->get_user_record())) {
                $sql = "SELECT COUNT('x')
                          FROM {$this->sql->from}
                         WHERE {$this->sql->where}
                           AND (x.gamification > :thegamification
                            OR (x.gamification = :thegamificationeq AND x.id < :theid))";
                $params = $this->sql->params + array(
                    'thegamification' => $record->gamification,
                    'thegamificationeq' => $record->gamification,
                    'theid' => $record->id
                );
                $count = $DB->count_records_sql($sql, $params);
                if ($count > 0) {
                    $this->currpage = floor($count / $pagesize);
                }
            }
        }

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

        $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->pagesize * $this->currpage, $this->pagesize);
    }

    /**
     * Query DB for the neighbours only.
     *
     * Note that this method resets and ignores pagination settings.
     *
     * @param int $userid The user to get the ladder for.
     * @param int $abovecount Number of neighbours to display above.
     * @param int $belowcount Number of neighbours to display below.
     * @return void
     */
    public function query_db_neighbours($userid, $abovecount, $belowcount) {
        global $DB;

        // First fetch self.
        $me = $this->get_user_record($userid);
        if (!$me) {
            $this->rawdata = [];
            return;
        }

        // Fetch the neighbours.
        $params = $this->sql->params + array(
            'neighid' => $me->id,
            'neighgamification' => $me->gamification,
            'neighgamificationeq' => $me->gamification,
        );
        $sqlabove = "SELECT {$this->sql->fields}
                       FROM {$this->sql->from}
                      WHERE {$this->sql->where}
                        AND (x.gamification > :neighgamification
                         OR (x.gamification = :neighgamificationeq AND x.id < :neighid))
                      ORDER BY x.gamification ASC, x.id DESC";
        $sqlbelow = "SELECT {$this->sql->fields}
                       FROM {$this->sql->from}
                      WHERE {$this->sql->where}
                        AND (x.gamification < :neighgamification
                         OR (x.gamification = :neighgamificationeq AND x.id > :neighid))
                      ORDER BY x.gamification DESC, x.id ASC";

        $records = [];
        $above = $DB->get_records_sql($sqlabove, $params, 0, $abovecount);
        foreach ($above as $record) {
            array_unshift($records, $record);
        }
        array_push($records, $me);
        $below = $DB->get_records_sql($sqlbelow, $params, 0, $belowcount);
        foreach ($below as $record) {
            array_push($records, $record);
        }

        // Set the raw data.
        $this->rawdata = $records;

        // No pagination.
        $count = count($records);
        $this->pagesize($count, $count);
    }

}
