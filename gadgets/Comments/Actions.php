<?php
/**
 * Comments Actions file
 *
 * @category    GadgetActions
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$actions = array();

$actions['Comments'] = array(
    'normal' => true,
    'file'   => 'Comments',
);

$actions['PostMessage'] = array(
    'normal' => true,
    'file'   => 'Comments',
);

$actions['Preview'] = array(
    'normal' => true,
    'file'   => 'Comments',
);

$actions['RecentComments'] = array(
    'normal' => true,
    'layout' => true,
    'file'   => 'RecentComments',
    'parametric' => true,
);

$actions['FeedsLink'] = array(
    'layout' => true,
    'parametric' => true,
    'file'   => 'Feeds',
);

$actions['RecentCommentsRSS'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);

$actions['RecentCommentsAtom'] = array(
    'standalone' => true,
    'file' => 'Feeds',
);
