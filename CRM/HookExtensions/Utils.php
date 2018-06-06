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

  static function getAddressById($address_id) {
    $address = new CRM_Core_BAO_Address();
    $address->id = $address_id;
    if (!$address->find(TRUE)) {
      throw new Exception("Could not find Address record with ID {$address_id}");
    }
    return $address;
  }

  static function getContactById($contact_id) {
    $contact = new CRM_Contact_BAO_Contact();
    $contact->id = $contact_id;
    if (!$contact->find(TRUE)) {
      throw new Exception("Could not find Contact record with ID {$contact_id}");
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

  static function getEmailByEmailAndContactId($email_str, $contact_id) {
    $email = new CRM_Core_BAO_Email();
    $email->email = $email_str;
    $email->contact_id = $contact_id;
    if ($email->email === NULL || $email->contact_id === NULL) {
      throw new Exception("getEmailbyEmailAndContactID requires email ($email_str) and contact_id ($contact_id), but one or both were NULL.");
    }
    if (!$email->find(TRUE)) {
      throw new Exception("Could not find Email record with email {$email->email} and contact_id {$email->contact_id}.");
    }
    return $email;
  }

  static function getEmailbyId($email_id) {
    $email = new CRM_Core_BAO_Email();
    $email->id = $email_id;
    if (!$email->find(TRUE)) {
      throw new Exception("Could not find Email record with ID {$email_id}");
    }
    return $email;
  }

  static function getGroupById($group_id) {
    $group = new CRM_Contact_BAO_Group();
    $group->id = $group_id;
    if (!$group->find(TRUE)) {
      throw new Exception("Could not find Group record with ID {$group_id}");
    }
    return $group;
  }

  static function getUFMatchById($ufmatch_id) {
    $ufmatch = new CRM_Core_BAO_UFMatch();
    $ufmatch->id = $ufmatch_id;
    if (!$ufmatch->find(TRUE)) {
      throw new Exception("Could not find UFMatch record with ID {$ufmatch_id}");
    }
    return $ufmatch;
  }
}
