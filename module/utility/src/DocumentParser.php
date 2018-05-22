<?php //-->
/**
 * This file is part of the Cradle PHP Library.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Utility;

/**
 * Typical model create action steps
 *
 * @vendor   Cradle
 * @package  Framework
 * @author   Ruje Alfon <ralfon@openovate.com>
 * @standard PSR-2
 */
class DocumentParser
{
    protected $filename;

    public function __construct($filePath)
    {
        $this->filename = $filePath;
    }

    public function convertToText()
    {
        if (isset($this->filename) && !file_exists($this->filename)) {
            return false;
        }

        $mimetype = mime_content_type($this->filename);

        if ($mimetype === 'application/msword') {
            return $this->readDoc();
        } else if ($mimetype === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return $this->readDocX();
        } else {
            return false;
        }
    }

    protected function readDoc()
    {
        if (($fh = fopen($this->filename, 'r')) !== false) {
            $headers = fread($fh, 0xA00);

            // 1 = (ord(n)*1) ; Document has from 0 to 255 characters
            $n1 = ( ord($headers[0x21C]) - 1 );

            // 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
            $n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 );

            // 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
            $n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 );

            // 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
            $n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 );

            // Total length of text in the document
            $textLength = ($n1 + $n2 + $n3 + $n4);

            if (filesize($this->filename) >= 0 && $textLength >= 0) {
                $extracted_plaintext = fread($fh, $textLength);

                // if you want to see your paragraphs in a new line, do this
                return nl2br($extracted_plaintext);
                //  return $extracted_plaintext;
            }

            return false;
        } else {
            return false;
        }
    }

    protected function readDocX()
    {
        $striped_content = '';
        $content = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) {
            return false;
        }

        while ($zip_entry = zip_read($zip)) {
            if (zip_entry_open($zip, $zip_entry) == false) {
                continue;
            }

            if (zip_entry_name($zip_entry) != 'word/document.xml') {
                continue;
            }

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', ' ', $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }
}
