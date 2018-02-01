<?php

namespace Drupal\nutripal\Tools;

use Psr\Log\LoggerInterface;

class ImportFoodData {

    /**
     * Alternative Excel reading format for CSV
     */
    const FILE_HEAD_ENCODING_UTF8 = "\xEF\xBB\xBF";

    /**
     * Native Excel Imp/Exp but have to decode/encode data from PHP default UTF-8 to UTF-16LE before Imp/Exp
     */
    const FILE_HEAD_ENCODING_UNICODE = "\xFF\xFE";

    private $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function logMessage($message) {
        $this->logger->notice($message);
    }

    public function parseFoodData($input_file, $input_head, $nbr_fields, $input_del = ' ', $input_enc = '"', $input_mod = 'rb') {
        if (($han_i = @fopen($input_file, $input_mod)) !== FALSE) {
            $row = 0;
            
            for ($headdump = 0; $headdump < strlen($input_head); $headdump++) {
                $this->logMessage("$input_file $row: head dump " . fgetc($han_i));
            }
            
            while (($data = fgetcsv($han_i, 0, $input_del, $input_enc)) !== FALSE) {
                $row++;
                
                $key_ind = 0;
                $key = (isset($data[$key_ind]) && !empty($data[$key_ind])) ? $data[$key_ind] : '?';
                $this->logMessage("$input_file $row: $key");
                
                if ($row == 1) {
                    $rc = 'skipping header at row 1...';
                    $this->logMessage("$input_file $row: $rc");
                    continue;
                } elseif (empty($data)) {
                    continue;
                } elseif (count($data) != $nbr_fields) {
                    $rc = "count $nbr_fields != " . count($data);
                    $this->logMessage("$input_file $row: $rc");
                    continue;
                }
                
                $id_import = $this->parseOneData($row, $data);
                $this->logMessage("$input_file $row: $key => $id_import");
            }
            fclose($han_i);
        } else {
            $this->logMessage("$input_file: failed to open stream.");
        }
    }

    public function parseOneData($id, $data) {
        $tax_name = $data[2];
        $ing_name = $data[3];
        $pictureu = $data[4];
        $picturet = $data[5];
        $content  = $data[11];
        
        $this->logMessage("$tax_name => $ing_name => $pictureu =>");
        return 0;
    }

}
