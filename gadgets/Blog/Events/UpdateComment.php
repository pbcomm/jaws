<?php
/**
 * Blog UpdateComment event
 *
 * @category   Gadget
 * @package    Blog
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Blog_Events_UpdateComment extends Jaws_Gadget_Event
{
    /**
     * Event execute method
     *
     */
    function Execute($gadget, $action, $reference)
    {
        if ($gadget != 'Blog') {
            return;
        }

        $cModel = $GLOBALS['app']->LoadGadget('Comments', 'Model', 'Comments');
        $howManyComment = $cModel->GetCommentsCount('Blog', $action, $reference, '',
            Comments_Info::COMMENT_STATUS_APPROVED);
        $bModel = $GLOBALS['app']->loadGadget('Blog', 'AdminModel', 'Comments');
        return $bModel->UpdatePostCommentsCount($reference, $howManyComment);
    }
}