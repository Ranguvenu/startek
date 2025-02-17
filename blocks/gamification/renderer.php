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
 * Block gamification renderer.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_gamification\local\course_world;
use block_gamification\local\activity\activity;
use block_gamification\local\utils\user_utils;
use block_gamification\local\gamification\level;
use block_gamification\local\gamification\level_with_badge;
use block_gamification\local\gamification\level_with_name;
use block_gamification\local\gamification\state;
use block_gamification\output\gamification_widget;

/**
 * Block gamification renderer class.
 *
 * @package    block_gamification
 * @copyright  2014 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_gamification_renderer extends plugin_renderer_base {

    /** @const Notice flag. */
    const NOTICE_FLAG_QUEST = 'block_gamification_notice_quest';

    /** @var string Notices flag. */
    protected $noticesflag = 'block_gamification_notices';

    /**
     * Advanced heading.
     *
     * @param string $heading The heading.
     * @param array $options The options.
     */
    public function advanced_heading($heading, $options = []) {
        $options = array_merge(['level' => 3, 'actions' => [], 'intro' => null, 'help' => null], $options);
        $level = (int) $options['level'];
        $actions = (array) $options['actions'];
        $intro = !empty($options['intro']) ? $options['intro'] : null;
        $help = $options['help'] instanceof \help_icon ? $options['help'] : null;

        $o = '';
        $o .= html_writer::start_div('gamification-flex gamification-mb-6 gamification-gap-4');
        $o .= html_writer::start_div('gamification-grow');
        $o .= html_writer::start_div('');
        $o .= $this->heading($heading, $level, 'gamification-m-0');
        if (!empty($intro)) {
            $o .= html_writer::start_div('gamification-text-sm gamification-text-gray-500 gamification-mt-2');
            $o .= $intro . ' ' . (!empty($help) ? $this->render($help) : '');
            $o .= html_writer::end_div();
        }
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();
        $o .= html_writer::start_div('gamification-flex gamification-flex-wrap gamification-gap-4 gamification-items-start gamification-whitespace-nowrap');
        $o .= implode('', array_map([$this, 'render'], $actions));
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();

        return $o;
    }

    /**
     * Render a control menu.
     *
     * @param action_menu_link $actions
     */
    public function control_menu($actions) {
        $menu = new action_menu();

        // Without this, the control menu can wrap on the next line when placed next to another item.
        $menu->attributes['class'] .= ' gamification-inline-block';

        // Styles copied from core_courseformat\output\local\content\cm::get_action_menu() in 4.1dev.
        $icon = $this->pix_icon('i/menu', get_string('menu', 'block_gamification'));
        $menu->set_menu_trigger($icon, 'btn btn-icon d-flex align-items-center justify-content-center after:gamification-hidden');

        foreach ($actions as $action) {
            $action->primary = false;
            $menu->add_secondary_action($action);
        }

        return $this->render($menu);
    }

    /**
     * Get a user's picture.
     *
     * @param object $user The user.
     * @param moodle_url The URL to the picture.
     */
    public function get_user_picture($user) {
        $pic = new user_picture($user);
        $pic->size = 1;
        return $pic->get_url($this->page);
    }

    /**
     * Print a level's badge.
     *
     * @param level $level The level.
     * @return string
     */
    public function level_badge(level $level) {
        return $this->level_badge_with_options($level);
    }

    /**
     * Small level badge.
     *
     * @param level $level The level.
     * @return string.
     */
    public function small_level_badge(level $level) {
        return $this->level_badge_with_options($level, ['small' => true]);
    }

    public function verysmall_level_badge(level $level) {
        return $this->level_badge_with_options($level, ['verysmall' => true]);
    }

    /**
     * Medium level badge.
     *
     * @param level $level The level.
     * @return string.
     */
    public function medium_level_badge(level $level) {
        return $this->level_badge_with_options($level, ['medium' => true]);
    }

    /**
     * Print a level's badge.
     *
     * @param level $level The level.
     * @return string
     */
    protected function level_badge_with_options(level $level, array $options = []) {
        $levelnum = $level->get_level();
        $classes = 'block_gamification-level level-' . $levelnum;
        $label = get_string('levelx', 'block_gamification', $levelnum);

        if (!empty($options['medium'])) {
            $classes .= ' medium';
        } else if (!empty($options['small'])) {
            $classes .= ' small';
        } else if(!empty($options['verysmall'])) {
            $classes .= ' verysmall';
        }

        $html = '';
        if ($level instanceof level_with_badge && ($badgeurl = $level->get_badge_url()) !== null) {
            $html .= html_writer::tag(
                'div',
                html_writer::empty_tag('img', ['src' => $badgeurl,
                    'alt' => $label]),
                ['class' => $classes . ' level-badge']
            );
        } else {
            $html .= html_writer::tag('div', $levelnum, ['class' => $classes, 'aria-label' => $label]);
        }
        return $html;
    }

    /**
     * Level name.
     *
     * @param level $level The level.
     * @param bool $force When forced, there will always be an name displayed.
     * @return string
     */
    public function level_name(level $level, $force = false) {
        $name = $level instanceof level_with_name ? $level->get_name() : null;
        if (empty($name) && $force) {
            $name = get_string('levelx', 'block_gamification', $level->get_level());
        }
        if (empty($name)) {
            return '';
        }
        return html_writer::tag('div', $name, ['class' => 'level-name']);
    }


    /**
     * Levels grid.
     *
     * @param array $levels The levels.
     * @return string
     */
    public function levels_grid(array $levels) {

        // If at least one level has a custom name, we will always show the name.
        $alwaysshowname = array_reduce($levels, function($carry, $level) {
            $name = $level instanceof \block_gamification\local\gamification\level_with_name ? $level->get_name() : '';
            return $carry + !empty($name) ? 1 : 0;
        }, 0) > 0;

        $o = '';
        $o .= html_writer::start_div('block_gamification-level-grid');
        foreach ($levels as $level) {
            $desc = $level instanceof \block_gamification\local\gamification\level_with_description ? $level->get_description() : '';
            $classes = ['block_gamification-level-boxed'];
            if ($desc) {
                $classes[] = 'block_gamification-level-boxed-with-desc';
            }

            $o .= html_writer::start_div(implode(' ', $classes));
            $o .= html_writer::start_div('block_gamification-level-box');
            $o .= html_writer::start_div('block_gamification-level-no');
            $o .= '#' . $level->get_level();
            $o .= html_writer::end_div();
            $o .= html_writer::start_div();
            $o .= $this->level_badge($level);
            $o .= html_writer::end_div();
            $o .= $this->level_name($level, $alwaysshowname);
            $o .= html_writer::start_div();
            $o .= $this->gamification($level->get_gamification_required());
            $o .= html_writer::end_div();
            $o .= html_writer::start_div('block_gamification-level-desc');
            $o .= $desc;
            $o .= html_writer::end_div();
            $o .= html_writer::end_div();
            $o .= html_writer::end_div();
        }
        $o .= html_writer::end_div();
        return $o;
    }

    /**
     * Levels preview.
     *
     * @param level[] $levels The levels.
     * @return string
     */
    public function levels_preview(array $levels) {
        $o = '';

        $o .= html_writer::start_div('block_gamification-level-preview');
        foreach ($levels as $level) {
            $o .= html_writer::start_div('previewed-level');
            $o .= html_writer::div('#' . $level->get_level(), 'level-name');
            $o .= $this->small_level_badge($level);
            $o .= html_writer::end_div();
        }
        $o .= html_writer::end_div();

        return $o;
    }

    /**
     * Return the notices.
     *
     * @param \block_gamification\local\course_world $world The world.
     * @return string The notices.
     */
    public function notices(\block_gamification\local\course_world $world) {
        global $CFG;
        $o = '';

        if (!$world->get_access_permissions()->can_manage()) {
            return $o;
        }

        $notice = null;
        $candidates = [
            [
                static::NOTICE_FLAG_QUEST,
                function() {
                    $questblogurl = new moodle_url('https://www.levelup.plus/blog/quest-moodle-gamification-plugin?ref=gamification_notice');
                    $questurl = new moodle_url('https://www.levelup.plus/quest?ref=gamification_notice');
                    return strip_tags(markdown_to_html(get_string('questreleasenotice', 'block_gamification', (object) array(
                        'questblogurl' => $questblogurl->out(false),
                        'questurl' => $questurl->out(false)
                    ))), '<a><em><strong>');
                }
            ], [
                $this->noticesflag,
                function() {
                    $moodleorgurl = new moodle_url('https://moodle.org/plugins/view.php?plugin=block_gamification');
                    $githuburl = new moodle_url('https://github.com/FMCorz/moodle-block_gamification');
                    return get_string('likenotice', 'block_gamification', (object) array(
                        'moodleorg' => $moodleorgurl->out(),
                        'github' => $githuburl->out()
                    ));
                }
            ]
        ];
        foreach ($candidates as $candidate) {
            if (!get_user_preferences($candidate[0], false)) {
                $notice = $candidate;
                break;
            }
        }

        if ($notice) {
            list($flag, $textfn) = $notice;

            require_once($CFG->libdir . '/ajax/ajaxlib.php');
            set_user_preference($flag, PARAM_BOOL);

            $this->page->requires->js_amd_inline("require([], function() {
                const flag = '$flag';
                const n = document.querySelector('.block-gamification-rocks');
                if (!n) return;
                n.addEventListener('click', function(e) {
                    e.preventDefault();
                    M.util.set_user_preference($flag, 1);
                    const notice = document.querySelector('.block-gamification-notices');
                    if (!notice) return;
                    notice.style.display = 'none';
                });
            });");

            $icon = new pix_icon('t/close', get_string('dismissnotice', 'block_gamification'), 'block_gamification');
            $actionicon = $this->action_icon(new moodle_url($this->page->url), $icon, null, array('class' => 'block-gamification-rocks'));

            $text = html_writer::start_div('gamification-flex gamification-gap-1');
            $text .= html_writer::div($textfn(), 'gamification-flex-1 [&_a]:gamification-font-normal [&_a]:gamification-underline');
            $text .= html_writer::div($actionicon, 'gamification-grow-0 dismiss-action');
            $text .= html_writer::end_div();

            $o .= html_writer::div($this->notification_without_close($text, 'success'),
                'block_gamification-dismissable-notice block-gamification-notices');
        }

        return $o;
    }

    /**
     * Outputs the navigation.
     *
     * This specifically requires a course_world and not a world.
     *
     * @param course_world $world The world.
     * @param string $page The page we are on.
     * @return string The navigation.
     * @deprecated Since Level Up gamification 3.12, use tab_navigation instead.
     */
    public function course_world_navigation(course_world $world, $page) {
        debugging('The method course_world_navigation is deprecated, please use tab_navigation instead.', DEBUG_DEVELOPER);
        $factory = \block_gamification\di::get('course_world_navigation_factory');
        $links = $factory->get_course_navigation($world);
        // If there is only one page, then that is the page we are on.
        if (count($links) <= 1) {
            return '';
        }
        return $this->tab_navigation($links, $page);
    }

    /**
     * Output a JSON script.
     *
     * @param mixed $data The data.
     * @param string $id The HTML ID to use.
     * @return string
     */
    public function json_script($data, $id) {
        $jsondata = json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
        return html_writer::tag('script', $jsondata, ['id' => $id, 'type' => 'application/json']);
    }

    /**
     * New dot.
     *
     * @return string
     */
    public function new_dot() {
        return html_writer::div(html_writer::tag('span', get_string('new'), ['class' => 'accesshide']), 'has-new');
    }

    /**
     * Print a notification without a close button.
     *
     * @param string|lang_string $message The message.
     * @param string $type The notification type.
     * @return string
     */
    public function notification_without_close($message, $type) {
        if (class_exists('core\output\notification')) {

            // Of course, it would be too easy if they didn't add and change constants
            // between two releases... Who reads the upgrade.txt, seriously?
            if (defined('core\output\notification::NOTIFY_INFO')) {
                $info = core\output\notification::NOTIFY_INFO;
            } else {
                $info = core\output\notification::NOTIFY_MESSAGE;
            }
            if (defined('core\output\notification::NOTIFY_SUCCESS')) {
                $success = core\output\notification::NOTIFY_SUCCESS;
            } else {
                $success = core\output\notification::NOTIFY_MESSAGE;
            }
            if (defined('core\output\notification::NOTIFY_WARNING')) {
                $warning = core\output\notification::NOTIFY_WARNING;
            } else {
                $warning = core\output\notification::NOTIFY_REDIRECT;
            }
            if (defined('core\output\notification::NOTIFY_ERROR')) {
                $error = core\output\notification::NOTIFY_ERROR;
            } else {
                $error = core\output\notification::NOTIFY_PROBLEM;;
            }

            $typemappings = [
                'success'           => $success,
                'info'              => $info,
                'warning'           => $warning,
                'error'             => $error,
                'notifyproblem'     => $error,
                'notifytiny'        => $error,
                'notifyerror'       => $error,
                'notifysuccess'     => $success,
                'notifymessage'     => $info,
                'notifyredirect'    => $info,
                'redirectmessage'   => $info,
            ];
        } else {
            // Old-style notifications.
            $typemappings = [
                'success'           => 'notifysuccess',
                'info'              => 'notifymessage',
                'warning'           => 'notifyproblem',
                'error'             => 'notifyproblem',
                'notifyproblem'     => 'notifyproblem',
                'notifytiny'        => 'notifyproblem',
                'notifyerror'       => 'notifyproblem',
                'notifysuccess'     => 'notifysuccess',
                'notifymessage'     => 'notifymessage',
                'notifyredirect'    => 'notifyredirect',
                'redirectmessage'   => 'redirectmessage',
            ];
        }

        $type = $typemappings[$type];

        if (class_exists('core\output\notification')) {
            $notification = new \core\output\notification($message, $type);
            if (method_exists($notification, 'set_show_closebutton')) {
                $notification->set_show_closebutton(false);
            }
            if (method_exists($notification, 'set_announce')) {
                $notification->set_announce(false);
            }
            return $this->render($notification);
        }

        return $this->notification($message, $type);
    }

    /**
     * Page size selector.
     *
     * @param array $options Array of [(int) $perpage, (moodle_url) $url].
     * @param int $current The current selectin.
     * @return string
     */
    public function pagesize_selector($options, $current) {
        $o = '';
        $o .= html_writer::start_div('text-right');
        $o .= html_writer::start_tag('small');
        $o .= get_string('perpagecolon', 'block_gamification') . ' ';

        $options = array_values($options);
        $lastindex = count($options) - 1;

        foreach ($options as $i => $option) {
            list($perpage, $url) = $option;
            $o .= $current == $perpage ? $current : html_writer::link($url, (string) $perpage);
            $o .= $i < $lastindex ? ' - ' : '';
        }

        $o .= html_writer::end_tag('small');
        $o .= html_writer::end_div();
        return $o;
    }

    /**
     * Override pix_url to auto-handle deprecation.
     *
     * It's just simpler than having to deal with differences between
     * Moodle < 3.3, and Moodle >= 3.3.
     *
     * @param string $image The file.
     * @param string $component The component.
     * @return string
     */
    public function pix_url($image, $component = 'moodle') {
        if (method_exists($this, 'image_url')) {
            return $this->image_url($image, $component);
        }
        return parent::pix_url($image, $component);
    }

    /**
     * Override render method.
     *
     * @param renderable $renderable The renderable.
     * @param array $options Options.
     * @return string
     */
    public function render(renderable $renderable, $options = array()) {
        if ($renderable instanceof block_gamification_ruleset) {
            return $this->render_block_gamification_ruleset($renderable, $options);
        } else if ($renderable instanceof block_gamification_rule) {
            return $this->render_block_gamification_rule($renderable, $options);
        }
        return parent::render($renderable);
    }

    /**
     * Renders a block gamification filter.
     *
     * Not very proud of the way I implement this... The HTML is tied to Javascript
     * and to the rule objects themselves. Careful when changing something!
     *
     * @param block_gamification_filter $filter The filter.
     * @return string
     */
    public function render_block_gamification_filter($filter) {
        static $i = 0;
        $o = '';
        $basename = 'filters[' . $i++ . ']';

        $o .= html_writer::start_tag('li', array('class' => 'filter', 'data-basename' => $basename));

        if ($filter->is_editable()) {
            $content = '';
            $content .= html_writer::start_div('gamification-flex gamification-min-h-10 gamification-group');

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render(new pix_icon('i/dragdrop', get_string('moverule', 'block_gamification'), '',
                array('class' => 'iconsmall filter-move')));
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-1 gamification-overflow-hidden gamification-min-h-full gamification-flex'
                . ' gamification-items-center gamification-leading-tight');
            $content .= get_string('awardagamificationwhen', 'block_gamification',
                html_writer::empty_tag('input', array(
                    'type' => 'number',//text
                    'value' => $filter->get_points(),
                    'size' => 3,
                    'name' => $basename . '[points]',
                    'class' => 'form-control block_gamification-form-control-inline !gamification-mr-1'))
            );
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render_block_gamification_rule_delete_action_link('deleterule', 'filter-delete');
            $content .= html_writer::end_div();

            $content .= html_writer::end_div();

            $o .= html_writer::div($content, 'gamification-group');
            $o .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'value' => $filter->get_id(),
                    'name' => $basename . '[id]'));
            $o .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'value' => $filter->get_sortorder(),
                    'name' => $basename . '[sortorder]'));
            $basename .= '[rule]';

        } else {
            $o .= html_writer::tag('p', get_string('awardagamificationwhen', 'block_gamification', $filter->get_points()));
        }
        $o .= html_writer::start_tag('ul', array('class' => 'filter-rules'));
        $o .= $this->render($filter->get_rule(), array('iseditable' => $filter->is_editable(), 'basename' => $basename));
        $o .= html_writer::end_tag('ul');
        $o .= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Renders a block gamification rule.
     *
     * @param block_gamification_rule_base $rule The rule.
     * @param array $options
     * @return string
     */
    public function render_block_gamification_rule($rule, $options) {
        static $i = 0;
        $iseditable = !empty($options['iseditable']);
        $basename = isset($options['basename']) ? $options['basename'] : '';
        if ($iseditable) {
            $content = '';
            $content .= html_writer::start_div('gamification-flex gamification-min-h-10');

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render(new pix_icon('i/dragdrop', get_string('movecondition', 'block_gamification'), '',
                ['class' => 'iconsmall rule-move']));
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-1 gamification-overflow-hidden gamification-min-h-full gamification-flex gamification-items-center'
                . ' gamification-leading-tight');
            $content .= html_writer::div($rule->get_form($basename), 'gamification-w-full gamification-max-w-full');
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render_block_gamification_rule_delete_action_link('deletecondition', 'rule-delete');
            $content .= html_writer::end_div();

            $content .= html_writer::end_div();
        } else {
            $content = s($rule->get_description());
        }
        $o = '';
        $o .= html_writer::start_tag('li', array('class' => 'rule rule-type-rule gamification-group'));
        $o .= html_writer::tag('div', $content, array('class' => 'rule-definition', 'data-basename' => $basename));
        $o .= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Renders a block gamification ruleset.
     *
     * @param block_gamification_ruleset $ruleset The ruleset.
     * @param array $options The options
     * @return string
     */
    public function render_block_gamification_ruleset($ruleset, $options) {
        static $i = 0;
        $iseditable = !empty($options['iseditable']);
        $basename = isset($options['basename']) ? $options['basename'] : '';
        $o = '';
        $o .= html_writer::start_tag('li', array('class' => 'rule rule-type-ruleset'));
        if ($iseditable) {
            $content = '';
            $content .= html_writer::start_div('gamification-flex gamification-min-h-10');

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render(new pix_icon('i/dragdrop', get_string('movecondition', 'block_gamification'), '',
                array('class' => 'iconsmall rule-move')));
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-1 gamification-overflow-hidden gamification-min-h-full gamification-flex'
                . ' gamification-items-center gamification-leading-tight');
            $content .= html_writer::div($ruleset->get_form($basename));
            $content .= html_writer::end_div();

            $content .= html_writer::start_div('gamification-flex-none gamification-h-10 gamification-flex gamification-items-center');
            $content .= $this->render_block_gamification_rule_delete_action_link('deletecondition', 'rule-delete');
            $content .= html_writer::end_div();

            $content .= html_writer::end_div();
        } else {
            $content = s($ruleset->get_description());
        }
        $o .= html_writer::tag('div', $content, array('class' => 'rule-definition gamification-h-10 gamification-group', 'data-basename' => $basename));
        $o .= html_writer::start_tag('ul', array('class' => 'rule-rules', 'data-basename' => $basename . '[rules]'));
        foreach ($ruleset->get_rules() as $rule) {
            if ($iseditable) {
                $options['basename'] = $basename . '[rules][' . $i++ . ']';
            }
            $o .= $this->render($rule, $options);
        }
        if ($iseditable) {
            $o .= html_writer::start_tag('li', array('class' => 'rule-add'));
            $o .= $this->action_link('#', get_string('addacondition', 'block_gamification'), null, null,
                new pix_icon('t/add', '', '', array('class' => 'iconsmall')));
            $o .= html_writer::end_tag('li');
        }
        $o .= html_writer::end_tag('ul');
        $o .= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Render the delete action icon in rules.
     *
     * @param string $str The string identifier for the title.
     * @param string $classname Classes to add to the action link.
     * @return string
     */
    public function render_block_gamification_rule_delete_action_link($str, $classname = '') {
        $icon = new pix_icon('t/delete', get_string($str, 'block_gamification'), '', ['class' => 'iconsmall']);
        return $this->action_link('#', '', null, [
            'class' => $classname . ' supports-hover:gamification-opacity-0 supports-hover:gamification-pointer-events-none'
                . ' focus:gamification-opacity-100 focus:gamification-pointer-events-auto'
                . ' group-hover:gamification-opacity-100 group-hover:gamification-pointer-events-auto gamification-transition-opacity'
        ], $icon);
    }

    /**
     * Render a dismissable notice.
     *
     * Yes, we cannot use CSS IDs in there because they are stripped out... turns out they
     * are considered dangerous. Oh well, we use a class instead. Not pretty, but it works...
     *
     * @param renderable $notice The notice.
     * @return string
     */
    public function render_dismissable_notice(renderable $notice) {
        $id = html_writer::random_id();

        // Tell the indicator that it should be egamificationecing this notice.
        $indicator = \block_gamification\di::get('user_notice_indicator');
        if ($indicator instanceof \block_gamification\local\indicator\user_indicator_with_acceptance) {
            $indicator->set_acceptable_user_flag($notice->name);
        }

        $url = \block_gamification\di::get('ajax_url_resolver')->reverse('notice/dismiss', ['name' => $notice->name]);
        $this->page->requires->js_init_call(<<<EOT
            Y.one('.$id .dismiss-action a').on('click', function(e) {
                e.preventDefault();
                Y.one('.$id').hide();
                var url = '$url';
                var cfg = {
                    method: 'POST'
                };
                Y.io(url, cfg);
            });
EOT
        );

        $icon = new pix_icon('t/close', get_string('dismissnotice', 'block_gamification'), 'block_gamification');
        $actionicon = $this->action_icon('#', $icon, null);
        $text = html_writer::div($actionicon, 'dismiss-action') . $notice->message;

        return html_writer::div($this->notification_without_close($text, $notice->type),
            'block_gamification-dismissable-notice ' . $id);
    }

    /**
     * Render the filters widget.
     *
     * /!\ We only support one editable widget per page!
     *
     * @param renderer_base $widget The widget.
     * @return string
     */
    public function render_filters_widget(renderable $widget) {
        $containerid = html_writer::random_id();

        if ($widget->editable) {
            $templatefilter = $this->render($widget->filter);

            $templatetypes = [];
            foreach ($widget->rules as $rule) {
                $templatetypes[] = [
                    'name' => $rule->name,
                    'info' => !empty($rule->info) ? $rule->info : null,
                    'template' => $this->render($rule->rule, ['iseditable' => true, 'basename' => 'XXXXX'])
                ];
            }

            // Prepare Javascript.
            $this->page->requires->yui_module('moodle-block_gamification-filters', 'Y.M.block_gamification.Filters.init', [[
                'containerSelector' => '#' . $containerid,
                'filter' => $templatefilter,
                'rules' => $templatetypes
            ]]);
            $this->page->requires->strings_for_js(['pickaconditiontype', 'deleterule', 'deletecondition'], 'block_gamification');
            $this->page->requires->strings_for_js(['areyousure'], 'core');
        }

        echo html_writer::start_div('block-gamification-filters-wrapper', ['id' => $containerid]);

        $addlink = '';
        if ($widget->editable) {
            $addlink = html_writer::start_tag('li', ['class' => 'filter-add']);
            $addlink .= $this->action_link('#', get_string('addarule', 'block_gamification'), null, null,
                new pix_icon('t/add', '', '', ['class' => 'iconsmall']));
            $addlink .= html_writer::end_tag('li');
        }

        $class = $widget->editable ? 'filters-editable' : 'filters-readonly';
        echo html_writer::start_tag('ul', ['class' => 'filters-list ' . $class]);
        echo $addlink;

        foreach ($widget->filters as $filter) {
            echo $this->render($filter);
            echo $addlink;
        }

        echo html_writer::end_tag('ul');

        echo html_writer::end_div();
    }

    /**
     * Render the filters widget group.
     *
     * @param rendererable $group The group.
     * @return string
     */
    public function render_filters_widget_element(renderable $element) {
        if (!empty($element->title)) {
            $title = $element->title . ($element->helpicon ? $this->render($element->helpicon) : '');
            echo html_writer::tag('h4', $title);
            if (!empty($element->description)) {
                echo $element->description;
            }
        }
        $this->render($element->widget);
    }

    /**
     * Render the filters widget group.
     *
     * @param rendererable $group The group.
     * @return string
     */
    public function render_filters_widget_group(renderable $group) {
        global $CFG;

        $formid = html_writer::random_id();

        // The form change checker YUI module is deprecated since Moodle 4.0.
        if ($CFG->branch >= 400) {
            $this->page->requires->js_call_amd('core_form/changechecker', 'watchFormById', [$formid]);
        } else {
            $this->page->requires->string_for_js('changesmadereallygoaway', 'moodle');
            $this->page->requires->yui_module('moodle-core-formchangechecker', 'M.core_formchangechecker.init',
                [['formid' => $formid]]);
        }

        echo html_writer::start_div('block-gamification-filters-group');
        echo html_writer::start_tag('form', ['method' => 'POST', 'class' => 'block-gamification-filters', 'id' => $formid]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

        $elements = $group->elements;
        foreach ($elements as $element) {
            echo $this->render($element);
        }

        echo html_writer::start_tag('p', ['class' => 'block-gamification-filters-submit-actions']);
        echo html_writer::empty_tag('input', ['value' => get_string('savechanges'), 'type' => 'submit', 'name' => 'save',
            'class' => 'btn btn-primary']);
        echo ' ';
        echo html_writer::empty_tag('input', ['value' => get_string('cancel'), 'type' => 'submit', 'name' => 'cancel',
            'class' => 'btn btn-default btn-secondary']);
        echo html_writer::end_tag('p');
        echo html_writer::end_tag('form');

        echo html_writer::end_div();
    }

    /**
     * Render a notice.
     *
     * @param renderable $notice The notice.
     * @return string
     */
    public function render_notice(renderable $notice) {
        return $this->notification_without_close($notice->message, $notice->type);
    }

    /**
     * Get the progress bar mustache context.
     *
     * @param state $state The renderable object.
     * @param bool $showpercentagetogo Show the percentage to go.
     * @return array
     */
    protected function get_progress_bar_context(state $state, $percentagetogo = false) {
        global $CFG;

        $pc = $state->get_ratio_in_level() * 100;
        $nextinvalue = $this->gamification($state->get_total_gamification_in_level() - $state->get_gamification_in_level());
        if ($percentagetogo) {
            $value = format_float(max(0, 100 - $pc), 1);
            // Quick hack to support localisation of percentages without having to define a new language
            // string for older versions. When the string is not available, we provide a sensible fallback.
            if ($CFG->branch >= 36) {
                $nextinvalue = get_string('percents', 'core', $value);
            } else {
                $nextinvalue = $value . '%';
            }
        }

        return [
            // 100% completion of a level is 0% of the next one, unless it's the final one.
            'atmaxlevel' => $pc >= 100,
            'nonfull' => $pc < 100,
            'nonzero' => $pc != 0,
            'percentage' => $pc,
            'percentagehuman' => $pc > 0 ? floor($pc) : ceil($pc),
            'nextinvaluehtml' => $nextinvalue
        ];
    }

    /**
     * Returns the progress bar rendered.
     *
     * @param state $state The renderable object.
     * @param bool $showpercentagetogo Show the percentage to go.
     * @return string HTML produced.
     */
    public function progress_bar(state $state, $percentagetogo = false) {
        return $this->render_from_template('block_gamification/progress-bar', $this->get_progress_bar_context($state, $percentagetogo));
    }

    /**
     * Initialise a react module.
     *
     * @param string $module The AMD name of the module.
     * @param object|array $props The props.
     * @return void
     */
    public function react_module($module, $props) {
        global $CFG;

        $id = html_writer::random_id('block_gamification-react-app');
        $propsid = html_writer::random_id('block_gamification-react-app-props');
        $iconname = $CFG->branch >= 32 ? 'y/loading' : 'i/loading';

        $o = '';
        $o .= html_writer::start_div('block_gamification-react', ['id' => $id]);
        $o .= html_writer::start_div('block_gamification-react-loading');
        $o .= html_writer::start_div();
        $o .= $this->render(new pix_icon($iconname, 'loading'));
        $o .= ' ' . get_string('loadinghelp');
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();
        $o .= html_writer::end_div();

        $o .= $this->json_script($props, $propsid);

        $this->page->requires->js_amd_inline("
            require(['block_gamification/launcher'], function(launcher) {
                launcher('$module', '$id', '$propsid');
            });
        ");

        return $o;
    }

    /**
     * Recent activity.
     *
     * @param activity[] $activity The activity entries.
     * @param moodle_url $moreurl The URL to view more (deprecated).
     * @return string
     */
    public function recent_activity(array $activity, moodle_url $moreurl = null) {
        return $this->render_from_template('block_gamification/recent-activity', [
            'hasrecentactivities' => !empty($activity),
            'recentactivities' => array_values(array_map(function($entry) {
                $gamification = $entry instanceof \block_gamification\local\activity\activity_with_gamification ? $entry->get_gamification() : null;
                return [
                    'date' => userdate($entry->get_date()->getTimestamp()),
                    'dateagotiny' => $this->tiny_time_ago($entry->get_date()),
                    'description' => $entry->get_description(),
                    'gamificationhtml' => $gamification !== null ? $this->gamification($gamification) : ''
                ];
            }, $activity)),
        ]);
    }

    /**
     * Render gamification widget.
     *
     * @param renderable $widget The widget.
     * @return string
     */
    public function render_gamification_widget(gamification_widget $widget) {
        if(is_siteadmin()){
            return $this->render_from_template('block_gamification/gamification-adminwidget', $widget->export_for_template($this));
        } else {
            return $this->render_from_template('block_gamification/gamification-widget', $widget->export_for_template($this));
        }
    }

    /**
     * Rules page loading check init.
     *
     * @return html
     */
    public function rules_page_loading_check_init() {
        return $this->render_from_template('block_gamification/rules-page-loading-error', []);
    }

    /**
     * Rules page loading check success.
     *
     * @return html
     */
    public function rules_page_loading_check_success() {
        return $this->render_from_template('block_gamification/rules-page-loading-success', []);
    }

    /**
     * Sub navigation.
     *
     * @return string
     */
    public function sub_navigation($items, $activenode) {
        return $this->render_from_template('block_gamification/sub-navigation', [
            'items' => array_map(function($item) use ($activenode) {
                $url = $item['url'];
                if ($url instanceof moodle_url) {
                    $url = $url->out(false);
                }
                return array_merge($item, [
                    'url' => $url,
                    'current' => $item['id'] == $activenode
                ]);
            }, $items)
        ]);
    }

    /**
     * Outputs the navigation.
     *
     * @param array $items The items.
     * @param string $activenode The active node.
     * @return string The navigation.
     */
    public function tab_navigation($items, $activenode) {
        $tabs = array_map(function($link) {
            // If we don't have a URL, but we have children take the first child's.
            if (empty($link['url']) && !empty($link['children'])) {
                $firstchild = reset($link['children']);
                $url = $firstchild['url'];
                $link = array_merge($link, ['url' => $url]);
            }
            return new tabobject($link['id'], $link['url'], $link['text'], clean_param($link['text'], PARAM_NOTAGS));
        }, array_filter($items, function($item) {
            // Remove the items that define children but do not have any.
            return !isset($item['children']) || !empty($item['children']);
        }));
        return html_writer::div($this->tabtree($tabs, $activenode), 'block_gamification-page-nav');
    }

    /**
     * Tiny time ago string.
     *
     * @param DateTime $dt The date object.
     * @return string
     */
    public function tiny_time_ago(DateTime $dt) {
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $dt->getTimestamp();
        $ago = '?';

        if ($diff < 15) {
            $ago = get_string('tinytimenow', 'block_gamification');
        } else if ($diff < 45) {
            $ago = get_string('tinytimeseconds', 'block_gamification', $diff);
        } else if ($diff < HOURSECS * 0.7) {
            $ago = get_string('tinytimeminutes', 'block_gamification', round($diff / 60));
        } else if ($diff < DAYSECS * 0.7) {
            $ago = get_string('tinytimehours', 'block_gamification', round($diff / HOURSECS));
        } else if ($diff < DAYSECS * 7 * 0.7) {
            $ago = get_string('tinytimedays', 'block_gamification', round($diff / DAYSECS));
        } else if ($diff < DAYSECS * 30 * 0.7) {
            $ago = get_string('tinytimeweeks', 'block_gamification', round($diff / (DAYSECS * 7)));
        } else if ($diff < DAYSECS * 365) {
            $ago = userdate($dt->getTimestamp(), get_string('tinytimewithinayearformat', 'block_gamification'));
        } else {
            $ago = userdate($dt->getTimestamp(), get_string('tinytimeolderyearformat', 'block_gamification'));
        }

        return $ago;
    }

    /**
     * Renders a user's avatar.
     *
     * This is similar to user_picture, except that it takes a URL
     * as argument instead of taking a user. It's egamificationected to be used
     * alongside text that describes the user, so that the avatar does
     * not need to be announced to screen readers.
     *
     * This always returns an image.
     *
     * @param moodle_url|null $url The URL.
     * @param moodle_url|null $link The link.
     * @return string
     */
    public function user_avatar(moodle_url $url = null, moodle_url $link = null) {
        if (!$url) {
            $url = user_utils::default_picture();
        }

        // Simulate the behaviour of user_picture.
        $img = html_writer::empty_tag('img', [
            'src' => $url->out(false),
            'role' => 'presentation',
            'class' => 'userpicture',
            'width' => 35,
            'height' => 35,
            'alt' => '',
        ]);

        if ($link) {
            return html_writer::link($link->out(false), $img, [
                'class' => 'd-inline-block aabtn'
            ]);
        }

        return $img;
    }

    /**
     * Format an amount of gamification.
     *
     * @param int $amount The gamification.
     * @return string
     */
    public function gamification($amount) {
        $gamification = $this->gamification_human($amount);
        $o = '';
        $o .= html_writer::start_tag('span', ['class' => 'block_gamification-gamification']);
        $o .= html_writer::tag('span', $gamification, ['class' => 'pts']);
        $o .= html_writer::tag('span', 'points', ['class' => 'sign sign-sup']);
        $o .= html_writer::end_tag('span');
        return $o;
    }

    /**
     * A highlight of the points.
     *
     * @param int $amount The gamification.
     */
    public function gamification_highlight($amount) {
        return html_writer::tag(
            'span',
            html_writer::tag('span', $this->gamification($amount), [
                'class' => 'gamification-inline-block gamification-bg-yellow-200 gamification-px-2 gamification-py-0.5 gamification-rounded-xl gamification-leading-none'
            ]),
            ['class' => 'block_gamification block_gamification-gamification-highlight']
        );
    }

    /**
     * Formats points for human.
     *
     * @param int $points
     */
    public function gamification_human($points) {
        $gamification = (int) $points;
        if (abs($gamification) > 999) {
            $thousandssep = get_string('thousandssep', 'langconfig');
            $gamification = number_format($gamification, 0, '.', $thousandssep);
        }
        return $gamification;
    }

    /**
     * Render gamification widget navigation.
     *
     * @param array $actions The actions.
     * @return string
     */
    public function gamification_widget_navigation(array $actions) {
        $o = '';
        $o .= html_writer::start_tag('nav');
        $o .= implode('', array_map(function(action_link $action) {
            $content = html_writer::div($this->render($action->icon));
            $content .= html_writer::div($action->text);
            return html_writer::link($action->url, $content, ['class' => 'nav-button']);
        }, $actions));
        $o .= html_writer::end_tag('nav');
        return $o;
    }

}
