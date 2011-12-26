<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail;

use Pop\File\File,
    Pop\Locale\Locale;

/**
 * @category   Pop
 * @package    Pop_Mail
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class Mail
{

    /**
     * Constant for text-only email
     * @var int
     */
    const TEXT = 1;

    /**
     * Constant for HTML-only email
     * @var int
     */
    const HTML = 2;

    /**
     * Constant for text and HTML email
     * @var int
     */
    const TEXT_HTML = 3;

    /**
     * Constant for text and file attachment email
     * @var int
     */
    const TEXT_FILE = 4;

    /**
     * Constant for HTML and file attachment email
     * @var int
     */
    const HTML_FILE = 5;

    /**
     * Constant for HTML and file attachment email
     * @var int
     */
    const TEXT_HTML_FILE = 6;

    /**
     * Sending queue
     * @var array
     */
    protected $_queue = array();

    /**
     * Subject
     * @var string
     */
    protected $_subject = null;

    /**
     * Message body
     * @var string
     */
    protected $_message = null;

    /**
     * Text part of the message body
     * @var string
     */
    protected $_text = null;

    /**
     * HTML part of the message body
     * @var string
     */
    protected $_html = null;

    /**
     * Mail headers
     * @var array
     */
    protected $_headers = array();

    /**
     * Mail parameters
     * @var string
     */
    protected $_params = null;

    /**
     * Character set
     * @var string
     */
    protected $_charset = 'utf-8';

    /**
     * MIME boundary
     * @var string
     */
    protected $_mimeBoundary = null;

    /**
     * File attachments
     * @var array
     */
    protected $_attachments = array();

    /**
     * Language object
     * @var Pop\Locale\Locale
     */
    protected $_lang = null;

    /**
     * Constructor
     *
     * Instantiate the mail object.
     *
     * @param  array  $rcpts
     * @param  string $subj
     * @return void
     */
    public function __construct(array $rcpts = null, $subj = null)
    {
        $this->_lang = new Locale();
        $this->_subject = $subj;

        if (null !== $rcpts) {
            $this->addRecipients($rcpts);
        }
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->_subject;
    }

    /**
     * Get MIME boundary
     *
     * @return string
     */
    public function getBoundary()
    {
        return $this->_mimeBoundary;
    }

    /**
     * Get character set
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->_charset;
    }

    /**
     * Get text part of the message.
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
    }

    /**
     * Get HTML part of the message.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     * Get the mail header
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get the mail header
     *
     * @param  string $name
     * @return string
     */
    public function getHeader($name)
    {
        return (isset($this->_headers[$name])) ? $this->_headers[$name] : null;
    }

    /**
     * Add recipients
     *
     * @param  array $rcpts
     * @return Pop\Mail\Mail
     */
    public function addRecipients(array $rcpts)
    {
        if (is_array($rcpts[0])) {
            foreach ($rcpts as $rcpt) {
                if (!array_key_exists('email', $rcpt)) {
                    throw new Exception($this->_lang->__("Error: At least one of the array keys must be 'email'."));
                } else {
                    $this->_queue[] = $rcpt;
                }
            }
        } else {
            if (!array_key_exists('email', $rcpts)) {
                throw new Exception($this->_lang->__("Error: At least one of the array keys must be 'email'."));
            } else {
                $this->_queue[] = $rcpts;
            }
        }

        return $this;
    }

    /**
     * Set the subject
     *
     * @param  string $subj
     * @return Pop\Mail\Mail
     */
    public function setSubject($subj)
    {
        $this->_subject = $subj;
        return $this;
    }

    /**
     * Set MIME boundary
     *
     * @param  string $bnd
     * @return Pop\Mail\Mail
     */
    public function setBoundary($bnd = null)
    {
        $this->_mimeBoundary = (null !== $bnd) ? $bnd : sha1(time());
        return $this;
    }

    /**
     * Set character set
     *
     * @param  string $chr
     * @return Pop\Mail\Mail
     */
    public function setCharset($chr)
    {
        $this->_charset = $chr;
        return $this;
    }

    /**
     * Set text part of the message.
     *
     * @param  string $txt
     * @return Pop\Mail\Mail
     */
    public function setText($txt)
    {
        $this->_text = $txt;
        return $this;
    }

    /**
     * Set HTML part of the message.
     *
     * @param  string $html
     * @return Pop\Mail\Mail
     */
    public function setHtml($html)
    {
        $this->_html = $html;
        return $this;
    }

    /**
     * Set a mail header
     *
     * @param  string $name
     * @param  string $value
     * @throws Exception
     * @return Pop\Mail\Mail
     */
    public function setHeader($name, $value)
    {
        if (is_array($value)) {
            if (isset($value['name']) && isset($value['email'])) {
                $this->_headers[$name] = $value['name'] . ' <' . $value['email'] . '>';
            } else if (isset($value[0]) && isset($value[1])) {
                $this->_headers[$name] = $value[0] . ' <' . $value[1] . '>';
            }
        } else {
            $this->_headers[$name] = $value;
        }

        return $this;
    }

    /**
     * Set mail headers
     *
     * @param  array $headers
     * @throws Exception
     * @return Pop\Mail\Mail
     */
    public function setHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * Attach a file to the mail object.
     *
     * @param  string|Pop\File\File $file
     * @throws Exception
     * @return Pop\Mail\Mail
     */
    public function attachFile($file)
    {
        // Determine if the file is valid.
        if (((is_string($file)) && (!file_exists($file))) && (!($file instanceof File))) {
            throw new Exception($this->_lang->__('Error: The parameter passed must either be a valid file or an instance of Pop\\File\\File.'));
        } else if (is_string($file)) {
            $fle = new File($file);
        } else {
            $fle = $file;
        }

        // Encode the file contents and set the file into the attachments array property.
        $contents = chunk_split(base64_encode($fle->read()));
        $this->_attachments[] = array('file' => $fle, 'contents' => $contents);

        return $this;
    }

    /**
     * Set parameters
     *
     * @param  string|array $params
     * @return Pop\Mail\Mail
     */
    public function setParams($params = null)
    {
        if (null === $params) {
            $this->_params = null;
        } else if (is_array($params)) {
            foreach ($params as $value) {
                $this->_params .= $value;
            }
        } else {
            $this->_params .= $params;
        }

        return $this;
    }

    /**
     * Initialize the email message.
     *
     * @throws Exception
     * @return void
     */
    public function init()
    {
        $msgType = $this->_getMessageType();

        if (null === $msgType) {
            throw new Exception($this->_lang->__('Error: The message body elements are not set.'));
        } else {
            $this->_message = null;
            $this->setBoundary();

            switch ($msgType) {
                // If the message contains files, HTML and text.
                case self::TEXT_HTML_FILE:
                    $this->setHeaders(array(
                        'MIME-Version' => '1.0',
                        'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . PHP_EOL . "This is a multi-part message in MIME format.",
                    ));

                    foreach ($this->_attachments as $file) {
                        $this->_message .= PHP_EOL . '--' . $this->getBoundary() .
                            PHP_EOL . 'Content-Type: file; name="' . $file['file']->basename .
                            '"' . PHP_EOL . 'Content-Transfer-Encoding: base64' . PHP_EOL .
                            'Content-Description: ' . $file['file']->basename . PHP_EOL .
                            'Content-Disposition: attachment; filename="' . $file['file']->basename .
                            '"' . PHP_EOL . PHP_EOL . $file['contents'] . PHP_EOL . PHP_EOL;
                    }

                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/html; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_html . PHP_EOL . PHP_EOL;

                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/plain; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_text . PHP_EOL . PHP_EOL . '--' .
                        $this->getBoundary() . '--' . PHP_EOL . PHP_EOL;

                    break;

                // If the message contains files and HTML.
                case self::HTML_FILE:
                    $this->setHeaders(array(
                        'MIME-Version' => '1.0',
                        'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . PHP_EOL . "This is a multi-part message in MIME format.",
                    ));

                    foreach ($this->_attachments as $file) {
                        $this->_message .= PHP_EOL . '--' . $this->getBoundary() .
                            PHP_EOL . 'Content-Type: file; name="' . $file['file']->basename .
                            '"' . PHP_EOL . 'Content-Transfer-Encoding: base64' . PHP_EOL .
                            'Content-Description: ' . $file['file']->basename . PHP_EOL .
                            'Content-Disposition: attachment; filename="' . $file['file']->basename .
                            '"' . PHP_EOL . PHP_EOL . $file['contents'] . PHP_EOL . PHP_EOL;
                    }
                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/html; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_html . PHP_EOL . PHP_EOL . '--' .
                        $this->getBoundary() . '--' . PHP_EOL . PHP_EOL;

                    break;

                // If the message contains files and text.
                case self::TEXT_FILE:
                    $this->setHeaders(array(
                        'MIME-Version' => '1.0',
                        'Content-Type' => 'multipart/mixed; boundary="' . $this->getBoundary() . '"' . PHP_EOL . "This is a multi-part message in MIME format.",
                    ));

                    foreach ($this->_attachments as $file) {
                        $this->_message .= PHP_EOL . '--' . $this->getBoundary() .
                            PHP_EOL . 'Content-Type: file; name="' . $file['file']->basename .
                            '"' . PHP_EOL . 'Content-Transfer-Encoding: base64' . PHP_EOL .
                            'Content-Description: ' . $file['file']->basename . PHP_EOL .
                            'Content-Disposition: attachment; filename="' . $file['file']->basename .
                            '"' . PHP_EOL . PHP_EOL . $file['contents'] . PHP_EOL . PHP_EOL;
                    }
                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/plain; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_text . PHP_EOL . PHP_EOL . '--' .
                        $this->getBoundary() . '--' . PHP_EOL . PHP_EOL;

                    break;

                // If the message contains HTML and text.
                case self::TEXT_HTML:
                    $this->setHeaders(array(
                        'MIME-Version' => '1.0',
                        'Content-Type' => 'multipart/alternative; boundary="' . $this->getBoundary() . '"' . PHP_EOL . "This is a multi-part message in MIME format.",
                    ));

                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/plain; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_text . PHP_EOL . PHP_EOL;
                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/html; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_html . PHP_EOL . PHP_EOL .
                        '--' . $this->getBoundary() . '--' . PHP_EOL . PHP_EOL;

                    break;

                // If the message contains HTML.
                case self::HTML:
                    $this->setHeaders(array(
                        'MIME-Version' => '1.0',
                        'Content-Type' => 'multipart/alternative; boundary="' . $this->getBoundary() . '"' . PHP_EOL . "This is a multi-part message in MIME format.",
                    ));

                    $this->_message .= '--' . $this->getBoundary() . PHP_EOL .
                        'Content-type: text/html; charset=' . $this->getCharset() .
                        PHP_EOL . PHP_EOL . $this->_html . PHP_EOL . PHP_EOL . '--' .
                        $this->getBoundary() . '--' . PHP_EOL . PHP_EOL;

                    break;

                // If the message contains text.
                case self::TEXT:
                    $this->setHeaders(array(
                        'Content-Type' => 'text/plain; charset="' . $this->getCharset()
                    ));

                    $this->_message = $this->_text . PHP_EOL;

                    break;

                // Else if nothing has been set yet
                default:
                    $this->_message = null;
            }
        }
    }

    /**
     * Send mail message or messages.
     *
     * This method depends on the server being set up correctly as an SMTP server
     * and sendmail being correctly defined in the php.ini file.
     *
     * @return void
     */
    public function send()
    {
        if (null === $this->_message) {
            $this->init();
        }

        $headers = $this->_buildHeaders() . PHP_EOL . PHP_EOL;

        // Iterate through the queue and send the mail messages.
        foreach ($this->_queue as $rcpt) {
            $subject = $this->_subject;
            $message = $this->_message;

            // Set the recipient parameter.
            $to = (isset($rcpt['name'])) ? $rcpt['name'] . " <" . $rcpt['email'] . ">" : $rcpt['email'];

            // Replace any set placeholder content within the subject or message.
            foreach ($rcpt as $key => $value) {
                $subject =  str_replace('[{' . $key . '}]', $value, $subject);
                $message =  str_replace('[{' . $key . '}]', $value, $message);
            }

            // Send the email message.
            mail($to, $subject, $message, $headers, $this->_params);
        }
    }

    /**
     * Get message type.
     *
     * @return string
     */
    protected function _getMessageType()
    {
        if ((count($this->_attachments) > 0) && (null === $this->_html) && (null === $this->_text)) {
            $type = null;
        } else if ((count($this->_attachments) > 0) && (null !== $this->_html) && (null !== $this->_text)) {
            $type = self::TEXT_HTML_FILE;
        } else if ((count($this->_attachments) > 0) && (null !== $this->_html)) {
            $type = self::HTML_FILE;
        } else if ((count($this->_attachments) > 0) && (null !== $this->_text)) {
            $type = self::TEXT_FILE;
        } else if ((null !== $this->_html) && (null !== $this->_text)) {
            $type = self::TEXT_HTML;
        } else if (null !== $this->_html) {
            $type = self::HTML;
        } else if (null !== $this->_text) {
            $type = self::TEXT;
        }

        return $type;
    }

    /**
     * Build headers
     *
     * @return string
     */
    protected function _buildHeaders()
    {
        $headers = null;
        foreach ($this->_headers as $key => $value) {
            $headers .= (is_array($value)) ? $key . ": " . $value[0] . " <" . $value[1] . ">" . PHP_EOL : $key . ": " . $value . PHP_EOL;
        }

        return $headers;
    }

}
