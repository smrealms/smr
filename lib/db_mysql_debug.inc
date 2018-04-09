<?php

  function table_names() {
    $this->query('SHOW TABLES');
    $i=0;
    while ($info=mysql_fetch_row($this->Query_ID))
     {
      $return[$i]['table_name']= $info[0];
      $return[$i]['tablespace_name']=$this->Database;
      $return[$i]['database']=$this->Database;
      $i++;
     }
   return $return;
  }

  /* public: sequence numbers */
  function nextid($seq_name) {
    $this->connect();

    if ($this->lockTable($this->Seq_Table)) {
      /* get sequence number (locked) and increment */
      $q  = sprintf('select nextid from %s where seq_name = \'%s\'',
                $this->Seq_Table,
                $seq_name);
      $id  = @mysql_query($q, $this->Link_ID);
      $res = @mysql_fetch_array($id);

      /* No current value, make one */
      if (!is_array($res)) {
        $currentid = 0;
        $q = sprintf('insert into %s values(\'%s\', %s)',
                 $this->Seq_Table,
                 $seq_name,
                 $currentid);
        $id = @mysql_query($q, $this->Link_ID);
      } else {
        $currentid = $res['nextid'];
      }
      $nextid = $currentid + 1;
      $q = sprintf('update %s set nextid = \'%s\' where seq_name = \'%s\'',
               $this->Seq_Table,
               $nextid,
               $seq_name);
      $id = @mysql_query($q, $this->Link_ID);
      $this->unlock();
    } else {
      $this->halt('cannot lock '.$this->Seq_Table.' - has it been created?');
      return 0;
    }
    return $nextid;
  }

  /* public: return table metadata */
  function metadata($table='',$full=false) {
    $count = 0;
    $id    = 0;
    $res   = array();

    /*
     * Due to compatibility problems with Table we changed the behavior
     * of metadata();
     * depending on $full, metadata returns the following values:
     *
     * - full is false (default):
     * $result[]:
     *   [0]['table']  table name
     *   [0]['name']   field name
     *   [0]['type']   field type
     *   [0]['len']    field length
     *   [0]['flags']  field flags
     *
     * - full is true
     * $result[]:
     *   ['num_fields'] number of metadata records
     *   [0]['table']  table name
     *   [0]['name']   field name
     *   [0]['type']   field type
     *   [0]['len']    field length
     *   [0]['flags']  field flags
     *   ['meta'][field name]  index of field named 'field name'
     *   The last one is used, if you have a field name, but no index.
     *   Test:  if (isset($result['meta']['myfield'])) { ...
     */

    // if no $table specified, assume that we are working with a query
    // result
    if ($table) {
      $this->connect();
      $id = @mysql_list_fields($this->Database, $table);
      if (!$id)
        $this->halt('Metadata query failed.');
    } else {
      $id = $this->Query_ID;
      if (!$id)
        $this->halt('No query specified.');
    }

    $count = @mysql_num_fields($id);

    // made this IF due to performance (one if is faster than $count if's)
    if (!$full) {
      for ($i=0; $i<$count; $i++) {
        $res[$i]['table'] = @mysql_field_table ($id, $i);
        $res[$i]['name']  = @mysql_field_name  ($id, $i);
        $res[$i]['type']  = @mysql_field_type  ($id, $i);
        $res[$i]['len']   = @mysql_field_len   ($id, $i);
        $res[$i]['flags'] = @mysql_field_flags ($id, $i);
      }
    } else { // full
      $res['num_fields']= $count;

      for ($i=0; $i<$count; $i++) {
        $res[$i]['table'] = @mysql_field_table ($id, $i);
        $res[$i]['name']  = @mysql_field_name  ($id, $i);
        $res[$i]['type']  = @mysql_field_type  ($id, $i);
        $res[$i]['len']   = @mysql_field_len   ($id, $i);
        $res[$i]['flags'] = @mysql_field_flags ($id, $i);
        $res['meta'][$res[$i]['name']] = $i;
      }
    }

    // free the result only if we were called on a table
    if ($table) @mysql_free_result($id);
    return $res;
  }
