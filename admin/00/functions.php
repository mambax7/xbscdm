<?php declare(strict_types=1);

use XoopsModules\Xbscdm\{
    Helper,
    Utility
};


/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Admin page functions
 *
 * @param mixed $forCodes
 * @copyright     XOOPS Project https://xoops.org/
 * @license       GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author        Ashley Kitson http://akitson.bbcb.co.uk
 * @author        XOOPS Development Team
 * @package       CDM
 * @subpackage    Admin
 * @access        private
 * @version       1
 * @copyright (c) 2004, Ashley Kitson
 */

/**
 * Function: Display list of Code Sets
 *
 * Display list of Code Sets to allow user to choose one to edit.
 * User can also create a new set
 *
 * @param bool $forCodes Is this form being called by the Codes administration screen
 * @version 1
 *
 */
function adminSelectSet($forCodes = false)
{
    /**
     * @global array user session
     */

    global $_SESSION;

    //Check to see if there are any Sets created yet.

    //If not then display a Code Set details input form

    // else allow user to select a Code Set

    $setHandler = Helper::getInstance()->getHandler('Set');

    if (0 == $setHandler->countSets()) {
        displaySetForm();
    } else {
        //check to see if user has already chosen a set previously

        $set_choice = $_SESSION['cd_set'] ?? CDM_DEF_SET;

        // Get data and assign to form

        $cd_set = new Xbscdm\Form\FormSelectSetAll(_AM_XBSCDM_SELSET, 'cd_set', $set_choice, 1);

        $submit = new \XoopsFormButton('', 'submit', _AM_XBSCDM_GO, 'submit');

        if ($forCodes) {
            $setForm = new \XoopsThemeForm(_AM_XBSCDM_SETFORM, 'setform', 'admincodes.php');
        } else {
            $insert = new \XoopsFormButton(_AM_XBSCDM_INSERT_DESC, 'insert', _AM_XBSCDM_INSERT, 'submit');

            $setForm = new \XoopsThemeForm(_AM_XBSCDM_SETFORM, 'setform', 'adminsets.php');
        }

        $setForm->addElement($cd_set, true);

        if ($forCodes) { //add a language selector
            $cd_lang = $_SESSION['cd_lang'] ?? CDM_DEF_LANG;

            $lang = new Xbscdm\Form\FormSelectLanguage(_AM_XBSCDM_SELLANG, 'cd_lang', $cd_lang, 1, $cd_lang);

            $setForm->addElement($lang);
        }

        $setForm->addElement($submit);

        if (!$forCodes) {
            $setForm->addElement($insert);
        }

        $setForm->display();
    }
}

//end function

/**
 * Function: Display Code Set meta details form
 *
 * @param string $set name of Code Set to edit or create a new one if null
 * @version 1
 *
 */
function displaySetForm($set = null)
{
    global $xoopsOption;

    //Cannot use smarty templates in admin yet (until xoops v2.2)

    //global $xoopsTpl;

    //$GLOBALS['xoopsOption']['template_main'] = _AM_SACC_EDITFORM;  // Set the template page to be used

    //Set up static text for form

    //$xoopsTpl->assign('lang_pagetitle',_AM_SACC_PAGETITLE1);

    //$xoopsTpl->assign('lang_copyright',_AM_SACC_COPYRIGHT);

    //retrieve meta details

    $metaHandler = Helper::getInstance()->getHandler('Meta');

    if (null != $set) {
        $metaData = $metaHandler->getAll($set);
    } else {
        $metaData = $metaHandler->create();
    }

    //set flag if record is defunct - very important as no changes are allowable

    // and we only show readonly text for a defunct record

    $isDefunct = (CDM_RSTAT_DEF == $metaData->getVar('row_flag'));

    //Set up form fields

    if (null === $set) {
        //if set=null then user has requested a new codeset setup

        $cd_set = new \XoopsFormText(_AM_XBSCDM_SETED1, 'cd_set', 10, 10, '');

        $new_flag  = new \XoopsFormHidden('new_flag', true); //tell POST process we are new
        $old_rstat = new \XoopsFormHidden('old_rstat', CDM_RSTAT_ACT); //set default old status
    } else { // else display the current set name as a label because it is primary key
        $cd_set = new \XoopsFormLabel(_AM_XBSCDM_SETED1, $metaData->getVar('cd_set'));

        $set_hid = new \XoopsFormHidden('cd_set', $metaData->getVar('cd_set')); //still need to know set name in POST process

        $new_flag = new \XoopsFormHidden('new_flag', false);

        $old_rstat = new \XoopsFormHidden('old_rstat', $metaData->getVar('row_flag')); //need to know old status when record saved
    }

    //end if

    if ($isDefunct) {
        $cd_type = new \XoopsFormLabel(_AM_XBSCDM_SETED2, $metaData->getVar('cd_type'));

        $cd_len = new \XoopsFormLabel(_AM_XBSCDM_SETED3, $metaData->getVar('cd_len'));

        $val_type = new \XoopsFormLabel(_AM_XBSCDM_SETED4, $metaData->getVar('val_type'));

        $val_len = new \XoopsFormLabel(_AM_XBSCDM_SETED5, $metaData->getVar('val_len'));

        $cd_desc = new \XoopsFormLabel(_AM_XBSCDM_SETED6, $metaData->getVar('cd_desc'));

        $row_flag = new \XoopsFormLabel(_AM_XBSCDM_RSTATNM, CDM_RSTAT_DEF);
    } else {
        $cd_type = new Xbscdm\Form\FormSelectFldType(_AM_XBSCDM_SETED2, 'cd_type', $metaData->getVar('cd_type'));

        $cd_len = new \XoopsFormText(_AM_XBSCDM_SETED3, 'cd_len', 3, 3, $metaData->getVar('cd_len'));

        $val_type = new Xbscdm\Form\FormSelectFldType(_AM_XBSCDM_SETED4, 'val_type', $metaData->getVar('val_type'));

        $val_len = new \XoopsFormText(_AM_XBSCDM_SETED5, 'val_len', 3, 3, $metaData->getVar('val_len'));

        $cd_desc = new \XoopsFormTextArea(_AM_XBSCDM_SETED6, 'cd_desc', $metaData->getVar('cd_desc'));

        $row_flag = new Xbscdm\Form\FormSelectRstat(_AM_XBSCDM_RSTATNM, 'row_flag', $metaData->getVar('row_flag'), 1, $metaData->getVar('row_flag'));
    }

    $ret = Xbscdm\Utility::getXoopsUser($metaData->getVar('row_uid'));

    $row_uid = new \XoopsFormLabel(_AM_XBSCDM_RUIDNM, $ret);

    $row_dt = new \XoopsFormLabel(_AM_XBSCDM_RDTNM, $metaData->getVar('row_dt'));

    $submit = new \XoopsFormButton('', 'save', _AM_XBSCDM_SUBMIT, 'submit');

    $cancel = new \XoopsFormButton('', 'cancel', _AM_XBSCDM_CANCEL, 'submit');

    $reset = new \XoopsFormButton('', 'reset', _AM_XBSCDM_RESET, 'reset');

    $editForm = new \XoopsThemeForm(_AM_XBSCDM_SETED0, 'metaform', 'adminsets.php');

    $editForm->addElement($cd_set);

    if (null != $set) {
        $editForm->addElement($set_hid);
    }

    $editForm->addElement($cd_type);

    $editForm->addElement($cd_len);

    $editForm->addElement($val_type);

    $editForm->addElement($val_len);

    $editForm->addElement($cd_desc);

    $editForm->addElement($new_flag);

    $editForm->addElement($old_rstat);

    $editForm->addElement($row_flag, true);

    $editForm->addElement($row_uid, false);

    $editForm->addElement($row_dt, false);

    //if the record is defunct then don't display submit button

    if (!$isDefunct) {
        $editForm->addElement($submit);
    }

    $editForm->addElement($cancel);

    //if the record is defunct then don't display reset button

    if (!$isDefunct) {
        $editForm->addElement($reset);
    }

    //$editForm->assign($xoopsTpl);

    $editForm->display();
} //end function displaySetForm

/**
 * Function: Save Set details
 *
 * Write Codeset data to database
 *
 * @version 1
 */
function submitSetForm()
{
    /**
     * @global array Form Post Variables
     */

    global $_POST;

    /**
     * @global array Session Variables
     */

    global $_SESSION;

    extract($_POST);

    $metaHandler = Helper::getInstance()->getHandler('Meta');

    if ($new_flag) { //create a new set
        $metaData = $metaHandler->create();

        $metaData->setVar('cd_set', $cd_set);
    } else { // retrieve old set
        $metaData = $metaHandler->getAll($cd_set);
    }

    //save new values to object

    $metaData->setVar('cd_type', $cd_type);

    $metaData->setVar('cd_len', $cd_len);

    $metaData->setVar('val_type', $val_type);

    $metaData->setVar('val_len', $val_len);

    $metaData->setVar('cd_desc', $cd_desc);

    if ((CDM_RSTAT_DEF != $old_rstat) and (CDM_RSTAT_DEF == $row_flag)) { //properly defunct the set and its child codes
        $metaData->setDefunct();
    } else {
        $metaData->setVar('row_flag', $row_flag);
    }

    /* defunct code records if meta record has been defuncted
    if (($old_rstat != CDM_RSTAT_DEF) AND ($row_flag == CDM_RSTAT_DEF)) { //properly defunct the record
        $orgHandler->loadAccounts($orgData);
        $orgData->setDefunct();
    } else {
        $orgData->setVar('row_flag',$row_flag);
    }
    */

    if (!$metaHandler->insert($metaData)) {
        $_SESSION['cd_set'] = $cd_set; //save code set for user
        if ((CDM_RSTAT_DEF != $old_rstat) and (CDM_RSTAT_DEF == $row_flag)) { //properly defunct the set and its child codes
            $setHandler = Helper::getInstance()->getHandler('Set');

            $setData = $setHandler->$getAll($cd_set);

            $codeHandler = Helper::getInstance()->getHandler('Code');

            $codes = $setData->getVar('code');

            foreach ($codes as $code) {
                $code->setDefunct();

                $codeHandler->insert($code);
            }
        }

        redirect_header(CDM_URL . '/admin/adminsets.php', 1, $metaHandler->getError());
    } else {
        redirect_header(CDM_URL . '/admin/adminsets.php', 1, _MD_XBSCDM_MEF15);
    }
    //end if
} //end function submitOrgForm

/**
 * Function: Edit an Code Set Meta data record
 *
 * Edit or create a new code set record
 *
 * @param int  $set  name of set to edit or create a new one if null
 * @param bool $save If true then save Set's details else display a form
 * @version 1
 *
 */
function adminEditSet($set = null, $save = false)
{
    if ($save) {
        submitSetForm($set);
    } else {
        displaySetForm($set);
    }
}

/**
 * Function: Select a code to edit
 *
 * List codes for a set and allow edit or insert of a new one
 * The function will ask for user to select a set first
 *
 * @param string $setName Id of set to display list of codes for. If null, ask user to select codeset
 * @param string $setLang language for code set
 *
 * @version 1
 */
function adminSelectCode($setName = null, $setLang = CDM_DEF_LANG)
{
    if (null === $setName) { //ask user to select a codeset
        adminSelectSet(true);
    } else { //display list of codes for the set
        $cd = new Xbscdm\Form\FormSelectAll($setName, _AM_XBSCDM_SELCODE, 'cd', null, 1, $setLang, 'cd');

        //check to see if a codes were returned

        $isCodes = !isset($cd->_options[-1]);

        $cd_set = new \XoopsFormHidden('cd_set', $setName);

        $cd_lang = new \XoopsFormHidden('cd_lang', $setLang);

        $cd_setdisp = new \XoopsFormLabel(_AM_XBSCDM_CODED1, $setName);

        $langName = Xbscdm\Utility::getCode('LANGUAGE', $setLang);

        $cd_langdisp = new \XoopsFormLabel(_AM_XBSCDM_CODED2, $langName);

        $submit = new \XoopsFormButton('', 'go', _AM_XBSCDM_GO, 'submit');

        $insert = new \XoopsFormButton(_AM_XBSCDM_INSERT_DESC, 'insert', _AM_XBSCDM_INSERT, 'submit');

        $codeForm = new \XoopsThemeForm(_AM_XBSCDM_CODEFORM, 'codeform', 'admincodes.php');

        $codeForm->addElement($cd_set, true);

        $codeForm->addElement($cd_lang, true);

        $codeForm->addElement($cd_setdisp, true);

        $codeForm->addElement($cd_langdisp, true);

        if ($isCodes) {
            $codeForm->addElement($cd);

            $codeForm->addElement($submit);
        }

        $codeForm->addElement($insert);

        $codeForm->display();
    }
}

/**
 * Function: Display the code edit form
 *
 * @param string $cd_set  Name of code set
 * @param string $cd      Name of code
 * @param string $cd_lang Name of code language
 * @version 1
 *
 */
function displayCodeForm($cd_set, $cd, $cd_lang = CDM_DEF_LANG)
{
    //cannot use smarty templates until xoops V2.2

    //$GLOBALS['xoopsOption']['template_main'] = _AM_SACC_EDITFORM;  // Set the template page to be used

    //Set up static text for form

    //$xoopsTpl->assign('lang_pagetitle',_AM_SACC_PAGETITLE4);

    //$xoopsTpl->assign('lang_copyright',_AM_SACC_COPYRIGHT);

    //Check to see if user logged in

    global $xoopsUser;

    if (empty($xoopsUser)) {
        redirect_header(CDM_URL . '/index.php?codeSet=' . CDM_DEF_SET, 1, _MD_XBSCDM_ERR_5);
    }

    $codeHandler = Helper::getInstance()->getHandler('Code');

    if (!empty($cd) && '' != $cd) { //retrieve the existing data object
        $id = $codeHandler->getKey($cd, $cd_set, $cd_lang);

        $codeData = $codeHandler->getAll($id);

        $isNew = false;
    } else { //create a new object
        $codeData = $codeHandler->create();

        $id = 0;

        $isNew = true;
    }

    //end if

    //check object instantiated and proceed

    if ($codeData) {
        //Set up form fields

        //first get the field info from the meta record

        $metaHandler = Helper::getInstance()->getHandler('Meta');

        $meta = $metaHandler->getAll($cd_set);

        $cd_len = (int)$meta->getVar('cd_len');

        $val_len = (int)$meta->getVar('val_len');

        $cd_set_disp = new \XoopsFormLabel(_MD_XBSCDM_CEF2, $cd_set);

        $cd_lang_disp = new \XoopsFormLabel(_MD_XBSCDM_CEF3, $cd_lang);

        $id = new \XoopsFormHidden('id', $id);

        $cd_lang = new \XoopsFormHidden('cd_lang', $cd_lang);

        $cd_set = new \XoopsFormHidden('cd_set', $cd_set);

        $new_flag = new \XoopsFormHidden('new_flag', $isNew); //tell POST process if we are new

        if ($isNew) { //if id = 0 then user has requested a new code
            //present the key fields in edit boxes

            $cd = new \XoopsFormText(_MD_XBSCDM_CEF1, 'cd', $cd_len, $cd_len, '');
        } else { // else display primary key as labels
            $cd_disp = new \XoopsFormLabel(_MD_XBSCDM_CEF1, $cd);

            $cd = new \XoopsFormHidden('cd', $cd);
        }

        //end if

        $cd_prnt = new \XoopsFormText(_MD_XBSCDM_CEF4, 'cd_prnt', $cd_len, $cd_len, $codeData->getVar('cd_prnt'));

        $cd_value = new \XoopsFormText(_MD_XBSCDM_CEF5, 'cd_value', $val_len, $val_len, $codeData->getVar('cd_value'));

        $cd_desc = new \XoopsFormTextArea(_MD_XBSCDM_CEF6, 'cd_desc', $codeData->getVar('cd_desc'));

        $cd_param = new \XoopsFormTextArea(_MD_XBSCDM_CEF16, 'cd_param', $codeData->getVar('cd_param'));

        $kids = new \XoopsFormLabel(_MD_XBSCDM_CEF17, $codeData->getKidsHtml());

        $row_flag = new Xbscdm\Form\FormSelectRstat(_MD_XBSCDM_CEF7, 'row_flag', $codeData->getVar('row_flag'), 1, $codeData->getVar('row_flag'));

        $ret = $xoopsUser->getUnameFromId($codeData->getVar('row_uid'), true);

        if (empty($ret)) { //if it didn't return a real name then get username/nickname
            $ret = $xoopsUser->getUnameFromId($codeData->getVar('row_uid'), false);
        }

        $row_uid = new \XoopsFormLabel(_MD_XBSCDM_CEF8, $ret);

        $row_dt = new \XoopsFormLabel(_MD_XBSCDM_CEF9, $codeData->getVar('row_dt'));

        $submit = new \XoopsFormButton('', 'save', _MD_XBSCDM_CEF10, 'submit');

        $cancel = new \XoopsFormButton('', 'cancel', _MD_XBSCDM_CEF11, 'submit');

        $reset = new \XoopsFormButton('', 'reset', _MD_XBSCDM_CEF12, 'reset');

        $codeForm = new \XoopsThemeForm(_MD_XBSCDM_CEF13, 'codeform', 'admincodes.php');

        //hidden elements

        $codeForm->addElement($cd_lang);

        $codeForm->addElement($id);

        $codeForm->addElement($cd_set);

        $codeForm->addElement($new_flag);

        //visible elements

        $codeForm->addElement($cd_set_disp);

        $codeForm->addElement($cd_lang_disp);

        if ($isNew) {
            $codeForm->addElement($cd, true);
        } else {
            $codeForm->addElement($cd, false);

            $codeForm->addElement($cd_disp);
        }

        $codeForm->addElement($cd_prnt, false);

        $codeForm->addElement($cd_value, true);

        $codeForm->addElement($cd_desc, false);

        $codeForm->addElement($cd_param, false);

        $codeForm->addElement($kids, false);

        $codeForm->addElement($row_flag, true);

        $codeForm->addElement($row_uid, false);

        $codeForm->addElement($row_dt, false);

        $codeForm->addElement($submit);

        $codeForm->addElement($cancel);

        $codeForm->addElement($reset);

        //$codeForm->assign($xoopsTpl);
        //require XOOPS_ROOT_PATH."/footer.php";
    } else {
        print $codeHandler->getError();
    }

    //end if

    //$editForm->assign($xoopsTpl);

    $codeForm->display();
} //end function displayCodeForm

/**
 * Function: Save a code record entry
 *
 * @version 1
 */
function submitCodeForm()
{
    global $_POST;

    extract($_POST); //retrieve posted data values

    $codeHandler = Helper::getInstance()->getHandler('Code');

    if (!$new_flag) { //retrieve the existing data object
        $codeData = $codeHandler->getAll($id);
    } else { //create a new object
        $codeData = $codeHandler->create();

        $codeData->setVar('cd_set', $cd_set);

        $codeData->setVar('cd_lang', $cd_lang);

        $codeData->setVar('cd', $cd);
    }

    //end if

    $codeData->setVar('cd_prnt', $cd_prnt);

    $codeData->setVar('cd_value', $cd_value);

    $codeData->setVar('cd_desc', $cd_desc);

    $codeData->setVar('cd_param', $cd_param);

    $codeData->setVar('row_flag', $row_flag);

    if (!$codeHandler->insert($codeData)) {
        redirect_header(CDM_URL . '/admin/admincodes.php', 10, $codeHandler->getError());
    } else {
        redirect_header(CDM_URL . '/admin/admincodes.php?gsubmit=1', 1, _AM_XBSCDM_CODED100);
    }
    //end if
}

/**
 * Function: Edit or save a code
 *
 * Displays code edit form or saves a code's details
 *
 * @param string $cd_set  Codeset name
 * @param string $cd      Code name
 * @param string $cd_lang Code language set
 * @param bool   $save    Set true if code details are to be saved
 * @version 1
 *
 */
function adminEditCode($cd_set = null, $cd = null, $cd_lang = CDM_DEF_LANG, $save = false)
{
    if ($save) {
        submitCodeForm();
    } else {
        displayCodeForm($cd_set, $cd, $cd_lang);
    }
}

/**
 * Function: Allow user to select a bulk code data file to upload
 *
 * Present input dialog so that user can select a SQL file for uploading codes to CDM database
 *
 * @version 1
 */
function adminSelectBulkUpload()
{
    /**
     * @global xoopsDB     Interface to database object
     * @global xoopsTpl    Smarty template object
     * @global xoopsOption Smarty options array
     * @global array       Form POST variable array
     * @global array       Form GET variable array
     * @global xoopsUser   Logged on user object
     */

    global $xoopsDB, $xoopsOption, $xoopsTpl, $_POST, $_GET, $xoopsUser;

    //can't use templates in 2.0.13 adminside

    //$GLOBALS['xoopsOption']['template_main'] = 'cdm_db_update.tpl';  // Set the template page to be used

    //require_once XOOPS_ROOT_PATH."/header.php";   // include the main header file

    //Check to see if user logged in

    if (empty($xoopsUser)) {
        redirect_header(CDM_URL . '/admin/adminupload.php', 1, _MD_XBSCDM_ERR_5);
    }

    /**
     * Xoops form objects
     */

    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    //Set up form fields

    $file = new \XoopsFormFile(_MD_XBSCDM_UDF1, 'fileloc', 0);

    $submit = new \XoopsFormButton('', 'submit', _MD_XBSCDM_UDF2, 'submit');

    $cancel = new \XoopsFormButton('', 'cancel', _MD_XBSCDM_UDF3, 'submit');

    $reset = new \XoopsFormButton('', 'reset', _MD_XBSCDM_UDF4, 'reset');

    $button_tray = new \XoopsFormElementTray('', '');

    $button_tray->addElement($submit);

    $button_tray->addElement($cancel);

    $button_tray->addElement($reset);

    $codeForm = new \XoopsThemeForm(_MD_XBSCDM_UDF0, 'form', 'adminupload.php');

    $codeForm->setExtra("enctype='multipart/form-data'");

    $codeForm->addElement($file);

    $codeForm->addElement($button_tray);

    //can't use templates in 2.0.13 adminside

    //$codeForm->assign($xoopsTpl);

    //require XOOPS_ROOT_PATH."/footer.php";

    $codeForm->display();
}

//end function adminSelectBulkUpload

/**
 * Function: Upload CDM bulk code sql file
 *
 * @param $fileloc
 * @version 1
 *
 */
function adminBulkUpload($fileloc)
{
    /**
     * @global xoopsDB     Interface to database object
     * @global xoopsTpl    Smarty template object
     * @global xoopsOption Smarty options array
     * @global array       Form POST variable array
     * @global array       Form GET variable array
     */

    global $xoopsDB, $xoopsOption, $xoopsTpl;

    extract($_POST);

    /**
     * file uploader utilities
     */

    require_once XOOPS_ROOT_PATH . '/class/uploader.php';

    /**
     * CDM functions
     */

    require_once CDM_PATH . '/include/functions.php';

    $allowed_mimetypes = ['text/plain'];

    $uploader = new \XoopsMediaUploader(XOOPS_ROOT_PATH . '/uploads', $allowed_mimetypes, 1048576);

    if ($uploader->fetchMedia($fileloc)) {
        if (!$uploader->upload()) {
            redirect_header(CDM_URL . '/admin/adminupload.php', 1, $uploader->getErrors());
        } else {
            $msg =  Utility::updateDatabase('uploads/' . $uploader->getSavedFileName(), 'modules/' . CDM_DIR . '/adminupload.php');

            redirect_header(CDM_URL . '/admin/adminupload.php', 5, $msg);
        }
    } else {
        redirect_header(CDM_URL . '//admin/adminupload.php', 1, $uploader->getErrors());
    }
}

//end function adminBulkUpload
