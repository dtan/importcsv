<?php

class ImportDriver_addresslocation extends ImportDriver_default {

    protected $type;
    protected $field;
    protected $coords = array();

    protected static $IS_MULTI_FIELD = true;
    protected static $PROCESS_FLAG = 'process';

    /**
     * Constructor
     * @return void
     */
    public function ImportDriver_addresslocation()
    {
        $this->type = 'addresslocation';
    }

    /**
     * Set a reference to the field object.
     * @param  $field   The field
     * @return void
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * Get the type of the driver
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function processFlag()
    {
        return self::$PROCESS_FLAG;
    }

    public function isMultiFieldImport()
    {
        return self::$IS_MULTI_FIELD;
    }

    /**
     * Create a key mapping of keys for the corresponding driver's keys
     * This is usually good when importing a csv that is a 1:many value:field ratio
     * @param {string} $key The key/value to get from the internal $map
     * @return array
     */
    public function getKeyMappings($key)
    {
        $key = strtolower($key);
        // add keys that should map to the corresponding driver's keys.
        $map = array(
            "address" => "street",
            "country_id" => "country",
            "zip" => "postal_code",
            "state" => "region"
        );
        $ret = isset($map[$key]) ? $map[$key] : $key;
        Symphony::Log()->writeToLog('getting key mapppings: ' . $key . ' :: ' . $ret);
        return $ret;
    }

    /**
     * Process the data so it can be imported into the entry.
     * @param  $value       The value to import
     * @param  $entry_id    If a duplicate is found, an entry ID will be provided.
     * @return The data returned by the field object
     */
    public function import($value, $entry_id = null)
    {
        $message = '';

        if (!$value['street'] || !$value['city'] || !$value['region'] || !$value['postal_code'] || !$value['country']) {
            // for the map location field, this value needs to come in as an array of two data points: [latitude, longitude]
            // so if it's not an array yet, return the latitude value first (must come in this order)
            // Symphony::Log()->writeToLog("value is not an array: ", $value);
            return $value;
        }
        $result = array(
            'street' => trim($value['street']),
            'city' => trim($value['city']),
            'region' => trim($value['region']),
            'postal_code' => trim($value['postal_code']),
            'country' => trim($value['country']),
        );

        $data = $this->field->processRawFieldData($result, $this->field->__OK__, $message, false, $entry_id);
        $data[self::$PROCESS_FLAG] = true;
        return $data;
    }

    /**
     * Process the data so it can be exported to a CSV
     * @param  $data    The data as provided by the entry
     * @param  $entry_id    The ID of the entry that is exported
     * @return string   A string representation of the data to import into the CSV file
     */
    public function export($data, $entry_id = null)
    {
        if(isset($data['value']))
        {
            if(!is_array($data['value']))
            {
                return trim($data['value']);
            } else {
                return trim(implode(array_filter($data['value'])));
            }
        } else {
            return '';
        }
    }

    /**
     * Scan the entries table for the next auto_increment ID.
     * However, if someone else is entering in data at the same time, then this could get out of sync
     * @param  $value       The value to scan for
     * @return null|string  The ID of the entry found, or null if no match is found.
     */
    public function scanDatabase($value)
    {
        $retID = null;
        $fieldID = $this->field->get('id');
        Symphony::Log()->writeToLog('field id: ' . $fieldID);
        foreach ($this->field as $meth => $val) {
            Symphony::Log()->writeToLog('field type: ' . $meth->_handle . ' :: ' . $meth->_name);
        }
        $result = Symphony::Database()->fetch('DESCRIBE `tbl_entries_data_' . $this->field->get('id') . '`;');
        foreach ($result as $tableColumn)
        {
            Symphony::Log()->writeToLog('field: ' . $tableColumn['Field']);
            if ($tableColumn['Field'] == 'street') {
                $searchResult = Symphony::Database()->fetchVar('entry_id', 0, sprintf('
                SELECT `entry_id`
                FROM `tbl_entries_data_%d`
                WHERE `street` = "%s";
            ',
                    $fieldID,
                    Symphony::Database()->cleanValue(trim($value))
                ));
                if ($searchResult != false) {
                    return $searchResult;
                }
            }
        }
        Symphony::Log()->writeToLog('All else failed');
        $entries = Symphony::Database()->fetch("SHOW TABLE STATUS LIKE 'tbl_entries'");
        foreach($entries as $row) {
            foreach($row as $field => $val) {
                if ($field == 'Auto_increment') {
                    Symphony::Log()->writeToLog($field . ' = ' . $val);
                    $retID = $val;
                }
            }
        }
        return $retID;
    }

}
