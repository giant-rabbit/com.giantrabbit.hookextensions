<?php

require_once 'hookextensions.civix.php';

function hookextensions_civicrm_config(&$config) {
  _hookextensions_civix_civicrm_config($config);
}

function hookextensions_civicrm_xmlMenu(&$files) {
  _hookextensions_civix_civicrm_xmlMenu($files);
}

function hookextensions_civicrm_install() {
  _hookextensions_civix_civicrm_install();
}

function hookextensions_civicrm_uninstall() {
  _hookextensions_civix_civicrm_uninstall();
}

function hookextensions_civicrm_enable() {
  _hookextensions_civix_civicrm_enable();
}

function hookextensions_civicrm_disable() {
  _hookextensions_civix_civicrm_disable();
}

function hookextensions_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _hookextensions_civix_civicrm_upgrade($op, $queue);
}

function hookextensions_civicrm_managed(&$entities) {
  _hookextensions_civix_civicrm_managed($entities);
}

function hookextensions_civicrm_caseTypes(&$caseTypes) {
  _hookextensions_civix_civicrm_caseTypes($caseTypes);
}

function hookextensions_civicrm_angularModules(&$angularModules) {
_hookextensions_civix_civicrm_angularModules($angularModules);
}

function hookextensions_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _hookextensions_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function hookextensions_civicrm_pre($op, $objectName, $id, &$params) {
  $objectName = hookextensions_normalizeObjectName($objectName);
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $id, $params, $op, $op, $op, "civicrm_pre_{$objectName}_{$op}");
  $hook_manager->invoke(3, $op, $id, $params, $op, $op, "civicrm_pre_{$objectName}");
}

function hookextensions_civicrm_pre_Contact_edit($contact_id, &$contact) {
  $old_contact = CRM_HookExtensions_Utils::getContactById($contact_id);
  hookextensions_static('old_contact', $old_contact);
}

function hookextensions_civicrm_pre_Email($op, $email_id, &$email) {
  // We don't want to run this processing if the full contact record is edited
  // so we check for a static variable we're setting in the contact pre edit
  // hook and only continue if that is not set.
  $old_contact = hookextensions_static('old_contact');
  if (!$old_contact) {
    // When an email is deleted, $email is empty, so we have to lookup the
    // contact ID.
    if (!empty($email['contact_id'])) {
      $contact_id = $email['contact_id'];
    }
    else {
      $email = CRM_HookExtensions_Utils::getEmailbyId($email_id);
      $contact_id = $email->contact_id;
    }
    $old_contact_from_email = CRM_HookExtensions_Utils::getContactById($contact_id);
    hookextensions_static('old_contact_from_email', $old_contact_from_email);
  }
}

function hookextensions_civicrm_pre_GroupContact_create($group_id, $contact_ids) {
  /* 
   * The create operation for GroupContact is thrown for every existing and
   * new group when a contact is saved, so we have to do some extra work
   * to determine whether the contact has just been added to the group or
   * not. We're storing the actually added Groups in a static variable for
   * use in the civicrm_post hook.
   */
  foreach ($contact_ids as $contact_id) {
    $contacts_added_to_group = array();
    $contact_added_to_group = CRM_HookExtensions_Utils::contactAddedToGroup($group_id, $contact_id);
    if ($contact_added_to_group) {
      $contacts_added_to_group[] = $contact_id;
    }
    hookextensions_static('contacts_added_to_group', $contacts_added_to_group);
  }
}

function hookextensions_civicrm_pre_Membership_delete($object_id, &$object_ref)
{
  $membership = new CRM_Member_BAO_Membership();
  $membership->id = $object_id;
  if (!$membership->find(TRUE))
  {
    throw new Exception("Unable to find membership {$membership->id} in smpte_kavi_civicrm_pre in order to check to see if Kavi needs to be update.");
  }
  hookextensions_static('deleted_membership', $membership);
}

function hookextensions_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  $objectName = hookextensions_normalizeObjectName($objectName);
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $objectId, $objectRef, $op, $op, $op, "civicrm_post_{$objectName}_{$op}");
  $hook_manager->invoke(3, $op, $objectId, $objectRef, $op, $op, "civicrm_post_{$objectName}");
}

function hookextensions_civicrm_post_GroupContact_create($group_id, &$contact_ids) {
  $contacts_added_to_group = hookextensions_static('contacts_added_to_group');
  if ($contacts_added_to_group) {
    $group = CRM_HookExtensions_Utils::getGroupById($group_id);
    foreach ($contacts_added_to_group as $contact_id) {
      $contact = CRM_HookExtensions_Utils::getContactById($contact_id);
      $hook_manager = CRM_Utils_Hook::singleton();
      $hook_manager->invoke(2, $group, $contact, $group, $group, $group, "civicrm_contact_added_to_group");
    }
  }
}

function hookextensions_civicrm_post_GroupContact_delete($group_id, &$contact_ids) {
  $group = CRM_HookExtensions_Utils::getGroupById($group_id);
  foreach ($contact_ids as $contact_id) {
    $contact = CRM_HookExtensions_Utils::getContactById($contact_id);
    $hook_manager = CRM_Utils_Hook::singleton();
    $hook_manager->invoke(2, $group, $contact, $group, $group, $group, "civicrm_contact_removed_from_group");
  }
}

function hookextensions_civicrm_post_Email($op, $email_id, &$email) {
  // We only want to run this if only the email address is changed since
  // we're already handling the case where the full contact is editied.
  $old_contact = hookextensions_static('old_contact_from_email');
  if ($old_contact) {
    $new_contact = CRM_HookExtensions_Utils::getContactById($old_contact->id);
    $hook_manager = CRM_Utils_Hook::singleton();
    $hook_manager->invoke(2, $old_contact, $new_contact, $op, $op, $op, "civicrm_contact_updated");
  }
}

function hookextensions_civicrm_post_Membership_create($object_id, &$membership)
{
  $contact = CRM_HookExtensions_Utils::getContactById($membership->contact_id);
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $membership, $contact, $object_id, $object_id, $object_id, "civicrm_contact_gained_membership");
}

function hookextensions_civicrm_post_Membership_delete($object_id, &$object_ref)
{
  $membership = hookextensions_static('deleted_membership');
  $contact = CRM_HookExtensions_Utils::getContactById($membership->contact_id);
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $membership, $contact, $object_id, $object_id, $object_id, "civicrm_contact_lost_membership");
}

function hookextensions_civicrm_post_Membership_edit($object_id, &$membership)
{
  $contact = CRM_HookExtensions_Utils::getContactById($membership->contact_id);
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $membership, $contact, $object_id, $object_id, $object_id, "civicrm_membership_updated");
}

function hookextensions_civicrm_post_Contact_edit($contact_id, &$contact) {
  $old_contact = hookextensions_static('old_contact');
  $new_contact = $contact;
  $hook_manager = CRM_Utils_Hook::singleton();
  $hook_manager->invoke(2, $old_contact, $new_contact, $contact_id, $contact_id, $contact_id, "civicrm_contact_updated");
}

function hookextensions_civicrm_post_Contact_delete($contact_id, &$contact) {
  $params = array("contact_id" => $contact_id);
  $contact_groups = CRM_Contact_BAO_Group::getGroups($params);
  foreach ($contact_groups as $group) {
    $hook_manager = CRM_Utils_Hook::singleton();
    $hook_manager->invoke(2, $group, $contact, $contact_id, $contact_id, $contact_id, "civicrm_contact_removed_from_group");
  }
}

function hookextensions_normalizeObjectName($objectName) {
  if ($objectName === "Individual" || $objectName === "Organization" || $objectName == "Household") {
    return "Contact";
  }
  return $objectName;
}

function hookextensions_static($name, $new_value = NULL, $reset = FALSE) {
  static $data = NULL;
  if ($reset) {
    $data = array();
    return $data;
  }
  if ($new_value !== NULL) {
    $data[$name] = $new_value;
  }
  if (isset($data[$name])) {
    return $data[$name];
  }
}
