<?php

class CRM_HookExtensions_Utils {
  static function contactAddedToGroup($group_id, $contact_id) {
    $query = "
      SELECT
      status
      FROM
      civicrm_group_contact
      WHERE
      group_id = %1
      AND
      contact_id = %2;
    ";
    $params = array(
      1 => array($group_id, 'Integer'),
      2 => array($contact_id, 'Integer')
    );
    $status = CRM_Core_DAO::singleValueQuery($query, $params);
    if ($status !== "Added") {
      return TRUE;
    }
    return FALSE;
  }

  static function getContactById($contact_id) {
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contact_id;
    if (!$contact->find(TRUE)) {
      throw new CRM_CiviMailchimp_Exception("Could not find Contact record with ID {$contact_id}");
    }
    $emails = new CRM_Core_BAO_Email();
    $emails->contact_id = $contact->id;
    $emails->find();
    while ($emails->fetch()) {
      $email = clone $emails;
      $contact->email[] = $email;
    }
    return $contact;
  }

  static function getEmailbyId($email_id) {
    $email = new CRM_Core_BAO_Email();
    $email->id = $email_id;
    if (!$email->find(TRUE)) {
      throw new CRM_CiviMailchimp_Exception("Could not find Email record with ID {$email_id}");
    }
    return $email;
  }

  static function getGroupById($group_id) {
    $group = new CRM_Contact_BAO_Group();
    $group->id = $group_id;
    if (!$group->find(TRUE)) {
      throw new CRM_CiviMailchimp_Exception("Could not find Group record with ID {$group_id}");
    }
    return $group;
  }
}
