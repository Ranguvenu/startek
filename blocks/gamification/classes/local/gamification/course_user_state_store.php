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
 * User state course store.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\local\gamification;

use context_helper;
use moodle_database;
use stdClass;
use block_gamification\local\logger\collection_logger_with_group_reset;
use block_gamification\local\logger\collection_logger_with_id_reset;
use block_gamification\local\logger\reason_collection_logger;
use block_gamification\local\observer\level_up_state_store_observer;
use block_gamification\local\observer\points_increased_state_store_observer;
use block_gamification\local\reason\reason;
use block_gamification\local\utils\user_utils;

/**
 * User state course store.
 *
 * This is a repository of gamification of each user. It also stores the level of
 * each user in the 'lvl' column, that only for ordering purposes. When
 * you change the levels_info, you must update the stored levels.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_user_state_store implements course_state_store,
        state_store_with_reason, state_store_with_delete {

    /** @var moodle_database The database. */
    protected $db;
    /** @var int The course ID. */
    protected $courseid;
    /** @var levels_info The levels info. */
    protected $levelsinfo;
    /** @var string The DB table. */
    protected $table = 'block_gamification';
    /** @var reason_collection_logger The logger. */
    protected $logger;
    /** @var level_up_state_store_observer The observer. */
    protected $observer;
    /** @var points_increased_state_store_observer The observer. */
    protected $pointsobserver;

    /**
     * Constructor.
     *
     * @param moodle_database $db The DB.
     * @param levels_info $levelsinfo The levels info.
     * @param int $courseid The course ID.
     * @param logger $logger The reason logger.
     * @param level_up_state_store_observer $observer The observer.
     * @param points_increased_state_store_observer $pointsobserver The observer.
     */
    public function __construct(moodle_database $db, levels_info $levelsinfo, $courseid,
            reason_collection_logger $logger, level_up_state_store_observer $observer = null,
            points_increased_state_store_observer $pointsobserver = null) {
        $this->db = $db;
        $this->levelsinfo = $levelsinfo;
        $this->courseid = $courseid;
        $this->logger = $logger;
        $this->observer = $observer;
        $this->pointsobserver = $pointsobserver;
    }

    /**
     * Get a state.
     *
     * @param int $id The object ID.
     * @return state
     */
    public function get_state($id) {
        $userfields = user_utils::picture_fields('u', 'userid');
        $contextfields = context_helper::get_preload_record_columns_sql('ctx');

        $sql = "SELECT u.id, x.userid, x.gamification, $userfields, $contextfields
                  FROM {user} u
                  JOIN {context} ctx
                    ON ctx.instanceid = u.id
                   AND ctx.contextlevel = :contextlevel
             LEFT JOIN {{$this->table}} x
                    ON x.userid = u.id
                   AND x.courseid = :courseid
                 WHERE u.id = :userid";

        $params = [
            'contextlevel' => CONTEXT_USER,
            'courseid' => $this->courseid,
            'userid' => $id
        ];

        return $this->make_state_from_record($this->db->get_record_sql($sql, $params, MUST_EXIST));
    }

    /**
     * Delete a state.
     *
     * @param int $id The object ID.
     * @return void
     */
    public function delete($id) {
        $params = [];
        $params['userid'] = $id;
        $params['courseid'] = $this->courseid;
        $this->db->delete_records($this->table, $params);

        if ($this->logger instanceof collection_logger_with_id_reset) {
            $this->logger->reset_by_id($id);
        }
    }

    /**
     * Return whether the entry exists.
     *
     * @param int $id The receiver.
     * @return stdClass|false
     */
    protected function exists($id) {
        $params = [];
        $params['userid'] = $id;
        $params['courseid'] = $this->courseid;
        return $this->db->get_record($this->table, $params);
    }

    /**
     * Add a certain amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     */
    public function increase($id, $amount) {
        $pregamification = 0;
        $postgamification = $amount;
        if($this->courseid != SITEID) {

            $worldfactory = \block_gamification\di::get('course_world_factory');
            $world = $worldfactory->get_world(SITEID);
            $levelsinfo = $world->get_levels_info();

            $recdata = $this->db->get_record_sql(
             "SELECT * 
                FROM {block_gamification} 
               WHERE userid = {$id} 
                 AND courseid = ".SITEID 
            );
            if($recdata) {
                $sql = "UPDATE {{$this->table}}
                           SET gamification = {$recdata->gamification} + $amount, redeemable  = {$recdata->redeemable} + $amount
                         WHERE courseid = ". SITEID ."
                           AND userid = $id";
                $params = [
                    'gamification' => $amount,
                    'courseid' => SITEID,
                    'userid' => $id
                ];

                $this->db->execute($sql);
                $newgamification = $recdata->gamification + $amount;

                $newlevel = $levelsinfo->get_level_from_gamification($newgamification)->get_level();
                if ($recdata->lvl != $newlevel) {
                    $this->db->set_field($this->table, 'lvl', $newlevel, ['courseid' => SITEID, 'userid' => $id]);
                }
            }else {
                $this->insertsitedata($id, $amount, $levelsinfo);
            }
        }
        if ($record = $this->exists($id)) {
            $pregamification = $record->gamification;
            $postgamification = $pregamification + $amount;

            $sql = "UPDATE {{$this->table}}
                       SET gamification = gamification + :gamification
                     WHERE courseid = :courseid
                       AND userid = :userid";
            $params = [
                'gamification' => $amount,
                'courseid' => $this->courseid,
                'userid' => $id
            ];
            $this->db->execute($sql, $params);
            // Non-atomic level update. We best guess what the gamification should be, and go from there.
            $newgamification = $record->gamification + $amount;
            $newlevel = $this->levelsinfo->get_level_from_gamification($newgamification)->get_level();
            if ($record->lvl != $newlevel) {
                $this->db->set_field($this->table, 'lvl', $newlevel, ['courseid' => $this->courseid, 'userid' => $id]);
            }
        } else {
            $this->insert($id, $amount);
        }
        $this->observe_increase($id, $pregamification, $postgamification);

        //Update weekly / monthly points
        $this->increase_weekly($id, $amount);
        $this->increase_monthly($id, $amount);
    }

    protected function increase_weekly($id, $amount) {
        $record = new stdClass();
        $record->courseid = SITEID;
        $record->userid = $id;
        $record->timeupdated = time();
        list($startdate, $enddate) = $this->week_dates();

        $exist = $this->db->get_record_sql(
            "SELECT * 
                FROM {block_gamification_weekly} 
                WHERE startdate <= {$startdate} 
                AND enddate = {$enddate}
                AND userid = {$id}
                ORDER BY id DESC 
                LIMIT 1"
            );

        if($exist) {
            $record->startdate = $exist->startdate;
            $record->enddate = $exist->enddate;
            $record->points = $exist->points + $amount;
            $record->id = $exist->id;
            $this->db->update_record('block_gamification_weekly', $record);
        } else {
            $record->startdate = $startdate;
            $record->enddate = $enddate;
            $record->points = $amount;
            $this->db->insert_record('block_gamification_weekly', $record);
        }
    }

    protected function increase_monthly($id, $amount) {
        $startdate = strtotime(date('Y-m-01'));
        $enddate = strtotime(date('Y-m-t'));

        $record = new stdClass();
        $record->courseid = SITEID;
        $record->userid = $id;
        $record->timeupdated = time();

        $exist = $this->db->get_record_sql(
            "SELECT * 
                FROM {block_gamification_monthly} 
                WHERE startdate <= {$startdate} 
                AND enddate = {$enddate}
                AND userid = {$id}
                ORDER BY id DESC 
                LIMIT 1"
            );
        if($exist) {
            $record->id = $exist->id;

            $record->startdate = $exist->startdate;
            $record->enddate = $exist->enddate;
            $record->points = $exist->points + $amount;
            $record->id = $exist->id;
            $this->db->update_record('block_gamification_monthly', $record);
        } else {
            $record->startdate = $startdate;
            $record->enddate = $enddate;
            $record->points = $amount;
            try{
                $rec = $this->db->insert_record('block_gamification_monthly', $record);
            } catch (\Exception $e) {
                echo 'Message: ' .$e->getMessage();
            }
        }
    }

    private function week_dates() {
        $today = new \DateTime();
        $today->modify('saturday');
        $saturday = strtotime($today->format('Y-m-d'));
        if(date('D') == 'Sun' && !$exist) {
            $startdate = strtotime(date('Y-m-d'));
            $enddate = $saturday;
        } else {
            $startdate = strtotime($today->modify('last sunday')->format('Y-m-d'));
            $enddate = $saturday;
        }

        return array($startdate, $enddate);
    }

    protected function insertsitedata($id, $amount, $levelsinfo) {
        $record = new stdClass();
        $record->courseid = SITEID;
        $record->userid = $id;
        $record->open_path = \core_user::get_user($id)->open_path;
        $record->gamification = $amount;
        $record->lvl = $levelsinfo->get_level_from_gamification($amount)->get_level();
        $record->redeemable = $amount;
        $this->db->insert_record($this->table, $record);
    }


    /**
     * Add a certain amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     * @param reason $reason A reason.
     */
    public function increase_with_reason($id, $amount, reason $reason) {
        $this->increase($id, $amount);
        $this->logger->log_reason($id, $amount, $reason);
    }

    /**
     * Insert the entry in the database.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     */
    protected function insert($id, $amount) {
        $record = new stdClass();
        $record->courseid = $this->courseid;
        $record->userid = $id;
        $record->open_path = \core_user::get_user($id)->open_path;
        $record->gamification = $amount;
        $record->lvl = $this->levelsinfo->get_level_from_gamification($amount)->get_level();
        $this->db->insert_record($this->table, $record);
    }

    /**
     * Make a user_state from the record.
     *
     * @param stdClass $record The row.
     * @param string $useridfield The user ID field.
     * @return user_state
     */
    public function make_state_from_record(stdClass $record, $useridfield = 'userid') {
        $user = user_utils::unalias_picture_fields($record, $useridfield);
        context_helper::preload_from_record($record);
        $gamification = !empty($record->gamification) ? $record->gamification : 0;
        return new user_state($user, $gamification, $this->levelsinfo, $this->courseid);
    }

    /**
     * Observe when increased.
     *
     * @param int $id The recipient.
     * @param int $beforegamification The points before.
     * @param int $aftergamification The points after.
     * @return void
     */
    protected function observe_increase($id, $beforegamification, $aftergamification) {
        $gamificationgained = $aftergamification - $beforegamification;

        if ($this->pointsobserver && $gamificationgained > 0) {
            $this->pointsobserver->points_increased($this, $id, $gamificationgained);
        }

        if ($this->observer) {
            $beforelevel = $this->levelsinfo->get_level_from_gamification($beforegamification);
            $afterlevel = $this->levelsinfo->get_level_from_gamification($aftergamification);
            if ($beforelevel->get_level() < $afterlevel->get_level()) {
                $this->observer->leveled_up($this, $id, $beforelevel, $afterlevel);
            }
        }
    }

    /**
     * Observe when set.
     *
     * @param int $id The recipient.
     * @param int $beforegamification The points before.
     * @param int $aftergamification The points after.
     * @return void
     */
    protected function observe_set($id, $beforegamification, $aftergamification) {
        if (!$this->observer) {
            return;
        }

        $beforelevel = $this->levelsinfo->get_level_from_gamification($beforegamification);
        $afterlevel = $this->levelsinfo->get_level_from_gamification($aftergamification);
        if ($beforelevel->get_level() < $afterlevel->get_level()) {
            $this->observer->leveled_up($this, $id, $beforelevel, $afterlevel);
        }
    }

    /**
     * Recalculate all the levels.
     *
     * Remember, these values are used for ordering only.
     *
     * @return void
     */
    public function recalculate_levels() {
        $rows = $this->db->get_recordset($this->table, ['courseid' => $this->courseid]);
        foreach ($rows as $row) {
            $level = $this->levelsinfo->get_level_from_gamification($row->gamification)->get_level();
            if ($level != $row->lvl) {
                $row->lvl = $level;
                $this->db->update_record($this->table, $row);
            }
        }
        $rows->close();
    }

    /**
     * Reset all experience points.
     *
     * @return void
     */
    public function reset() {
        $this->db->delete_records($this->table, ['courseid' => $this->courseid]);
        $this->logger->reset();
    }

    /**
     * Reset all experience for users in a group.
     *
     * @param int $groupid The group ID.
     * @return void
     */
    public function reset_by_group($groupid) {
        $sql = "DELETE
                  FROM {{$this->table}}
                 WHERE courseid = :courseid
                   AND userid IN
               (SELECT gm.userid
                  FROM {groups_members} gm
                 WHERE gm.groupid = :groupid)";

        $params = [
            'courseid' => $this->courseid,
            'groupid' => $groupid
        ];

        $this->db->execute($sql, $params);

        if ($this->logger instanceof collection_logger_with_group_reset) {
            $this->logger->reset_by_group($groupid);
        }
    }

    /**
     * Set the amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     */
    public function set($id, $amount) {
        $pregamification = 0;
        $postgamification = $amount;

        if ($record = $this->exists($id)) {
            $pregamification = $record->gamification;
            $postgamification = $pregamification + $amount;

            $sql = "UPDATE {{$this->table}}
                       SET gamification = :gamification,
                           lvl = :lvl
                     WHERE courseid = :courseid
                       AND userid = :userid";
            $params = [
                'gamification' => $amount,
                'courseid' => $this->courseid,
                'userid' => $id,
                'lvl' => $this->levelsinfo->get_level_from_gamification($amount)->get_level()
            ];
            $this->db->execute($sql, $params);
        } else {
            $this->insert($id, $amount);
        }

        $this->observe_set($id, $pregamification, $postgamification);
    }

    /**
     * Set the amount of experience points.
     *
     * @param int $id The receiver.
     * @param int $amount The amount.
     * @param reason $reason A reason.
     */
    public function set_with_reason($id, $amount, reason $reason) {
        $this->set($id, $amount);
        $this->logger->log_reason($id, $amount, $reason);
    }

}
