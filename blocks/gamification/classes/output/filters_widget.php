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
 * Filters widget.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_gamification\output;

use coding_exception;
use block_gamification_filter;
use block_gamification_rule;
use renderable;

/**
 * Filters widget class.
 *
 * @package    block_gamification
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filters_widget implements renderable {

    /** @var bool Whether we can add more filters to this widget. */
    public $canaddmore = true;
    /** @var bool Editable? */
    public $editable = true;
    /** @var block_gamification_filter An empty filter to get a template from. */
    public $filter;
    /** @var block_gamification_filter[] Array of existinf filters. */
    public $filters;
    /** @var array Of objects containing name and rule instances. */
    public $rules;

    /**
     * Constructor.
     *
     * @param object $filter The default filter.
     * @param array $rules Of objects containing:
     *                     - (string) name
     *                     - (block_gamification_rule) rule
     * @param block_gamification_filter[] $filters The contained filters.
     * @param bool $editable Whether this is editable.
     * @param array $options Additional options.
     */
    public function __construct(block_gamification_filter $filter = null, array $rules = [], array $filters = [], $editable = true,
            array $options = []) {
        $this->editable = $editable;
        $this->filter = $filter;
        $this->rules = $rules;
        $this->filters = $filters; //comment by revathi
        $this->canaddmore = isset($options['canaddmore']) ? $options['canaddmore'] : true;

        // Internal validation is rather bad, we could do better.
        if ($editable) {
            if (!$filter || !$rules) {
                throw new coding_exception('An editable filter must contain filter and rules.');
            }
        }

        foreach ($rules as $rule) {
            if (!isset($rule->name)) {
                throw new coding_exception('Each rule must have a readable name.');
            } else if (!isset($rule->rule) || !$rule->rule instanceof block_gamification_rule) {
                throw new coding_exception('A rule was not found, or not valid.');
            }
        }
    }

}
