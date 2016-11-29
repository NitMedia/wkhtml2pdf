<?php namespace Nitmedia\Wkhtml2pdf;

use \Exception;

/**
 * This class was produced by PHP-4-Business.co.uk and is based on the classes from
 * aur1mas <aur1mas@devnet.lt>  --  https://github.com/aur1mas/Wkhtmltopdf
 *      and
 * uuf6429@gmail.com / contact@covac-software.com  --  http://code.google.com/p/wkhtmltopdf/wiki/IntegrationWithPhp
 *
 * Authorship and copyright of those classes is unclear - they both claim authorship although the code is largely identical!
 * From:
 *   https://github.com/aur1mas/Wkhtmltopdf
 *   @author Aurimas Baubkus aka aur1mas <aur1mas@devnet.lt>
 *   @license Released under "New BSD license"
 * and
 *   http://code.google.com/p/wkhtmltopdf/wiki/IntegrationWithPhp
 *   @copyright 2010 Christian Sciberras / Covac Software.
 *   @license None. There are no restrictions on use, however keep copyright intact.
 *   Modification is allowed, keep track of modifications below in this comment block.
 *
 * Raw settings
 *      http://www.cs.au.dk/~jakobt/libwkhtmltox_0.10.0_doc/pagesettings.html
 *
 * When using output() the mode param takes one of 4 values:
 *
 * 'D'  (const MODE_DOWNLOAD = 'D')  - Force the client to download PDF file
 * 'S'  (const MODE_STRING = 'S')    - Returns the PDF file as a string
 * 'I'  (const MODE_EMBEDDED = 'I')  - When possible, force the client to embed PDF file
 * 'F'  (const MODE_SAVE = 'F')      - PDF file is saved on the server. The path+filename is returned.
 *
 * But note that the user's browser settings may override what you ask for!
 *
 */

/**
 * @version 2
 */
class Wkhtml2pdf
{
    /**
     * Setters / getters properties
     */
    protected $_method = null;
    protected $_html = null;
    protected $_httpurl = null;
    protected $_orientation = null;
    protected $_pageSize = null;
    protected $_toc = false;
    protected $_copies = 1;
    protected $_grayscale = false;
    protected $_title = null;
    protected $_headerHtml;
    protected $_footerHtml;
    protected $_httpusername;
    protected $_httppassword;
    protected $_options;

    /**
     * What type of input are we processing?  (url / disc file / html string)
     *
     * @var unknown_type
     */
    protected $_have_httpurl = false;
    protected $_have_htmlfile = false;
    protected $_have_html = false;

    /**
     * Location of wkhtmltopdf executable
     */
    protected $_binpath = '/usr/bin/';
    protected $_binname = 'wkhtmltopdf';

    /**
     * Location of HTML file
     */
    protected $_htmlfilepath = '/tmp/';
    protected $_htmlfilename = null;
    protected $_tmphtmlfilename = null;

    /**
     * Directory to use for temporary files
     */
    protected $_tmpfilepath = '/tmp/';

    /**
     * Temporary files holding header / footer HTML
     */
    protected $_have_headerhtml = false;
    protected $_have_footerhtml = false;
    protected $_headerfilename = null;
    protected $_footerfilename = null;

    /**
     * Available page orientations
     */
    const ORIENTATION_PORTRAIT = 'Portrait';    // vertical
    const ORIENTATION_LANDSCAPE = 'Landscape';  // horizontal

    /**
     * Page sizes
     */
    const SIZE_A4 = 'A4';
    const SIZE_LETTER = 'letter';

    /**
     * PDF get modes
     */
    const MODE_DOWNLOAD = 'D';              // Force the client to download PDF file
    const MODE_STRING = 'S';                // Returns the PDF file as a string
    const MODE_EMBEDDED = 'I';              // When possible, force the client to embed PDF file
    const MODE_SAVE = 'F';                  // PDF file is saved on the server. The path+filename is returned.

    protected $_outputMode = 'I';

    /**
     * Illuminate config repository.
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Illuminate view environment.
     *
     * @var ViewInterface
     */
    protected $view;

    /**
     * Constructor: initialize command line and reserve temporary file.
     * @param array|\Nitmedia\Wkhtml2pdf\ConfigInterface $config
     * @param $view
     * @throws \Exception
     * @return \Nitmedia\Wkhtml2pdf\Wkhtml2pdf FALSE on failure
     */
    public function __construct(ConfigInterface $config, ViewInterface $view)
    {
        $this->config = $config;
        $this->view = $view;

	$binpath = $this->config->get('binpath');

        if ($binpath)
        {
            /* Check for Absolute paths in *nix and Windows */
            if( $binpath[0] === '/' || $binpath[1] === ':')
            {
                $this->setBinPath($this->config->get('binpath'));
            }
            else
            {
                $this->setBinPath( realpath(__DIR__) . '/' . $this->config->get('binpath'));
            }
        }

        if ($this->config->get('binfile'))
        {
            $this->setBinFile($this->config->get('binfile'));
        }

        /* Check the binary executable exists */
        $this->getBin();

        if ($this->config->get('html'))
        {
            $this->setHtml($this->config->get('html'));
        }

        if ($this->config->get('orientation'))
        {
            $this->setOrientation($this->config->get('orientation'));
        }
        else
        {
            $this->setOrientation(self::ORIENTATION_PORTRAIT);
        }

        if ($this->config->get('page_size'))
        {
            $this->setPageSize($this->config->get('page_size'));
        }
        else
        {
            $this->setPageSize(self::SIZE_A4);
        }

        if ($this->config->get('toc'))
        {
            $this->setTOC($this->config->get('toc'));
        }

        if ($this->config->get('grayscale'))
        {
            $this->setGrayscale($this->config->get('grayscale'));
        }

        if ($this->config->get('title'))
        {
            $this->setTitle($this->config->get('title'));
        }

        if ($this->config->get('debug'))
        {
            $this->debug = $this->config->get('debug');
        }

        if ($this->config->get('header_html'))
        {
            $this->setHeaderHtml($this->config->get('header_html'));
        }

        if ($this->config->get('footer_html'))
        {
            $this->setFooterHtml($this->config->get('footer_html'));
        }

        if ($this->config->get('tmppath'))
        {
            $this->setTmpPath($this->config->get('tmppath'));
        }

        if ($this->config->get('output_mode'))
        {
            $this->setOutputMode($this->config->get('output_mode'));
        }

        if ($this->config->get('options'))
        {
            $this->setOptions($this->config->get('options'));
        }
    }

    /**
     * @param string $view
     * @param array $data
     * @param string $name
     * @throws \Exception
     */
    public function html($view, $data= array(), $name='file')
    {
        $this->setHtml($this->view->make($view,$data));
        return $this->output($this->getOutputMode(), $name . ".pdf");
    }

    public function url($url, $name='file')
    {
        $this->setHttpUrl($url);
        return $this->output($this->getOutputMode(), $name . ".pdf");
    }

    /**
     * Attempts to return the library's full help info
     *
     * @return string
     */
    public function getHelp()
    {
        $r = $this->_exec($this->getBin() . " --extended-help");
        return $r['stdout'];
    }

    /**
     * Set path to binary executable directory
     *
     * @throws Exception
     * @return null
     */
    public function setBinPath($path)
    {
        if (realpath($path) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($path,ENT_QUOTES).'")');

        $this->_binpath = realpath((string)$path) . DIRECTORY_SEPARATOR;
        return;
    }

    /**
     * Get path to binary executable
     *
     * @return string
     */
    public function getBinPath()
    {
        return $this->_binpath;
    }

    /**
     * Set filename of binary executable
     *
     * @return null
     */
    public function setBinFile($name)
    {
        $this->_binname = (string)$name;
        return;
    }

    /**
     * Get filename of binary executable
     *
     * @return string
     */
    public function getBinFile()
    {
        return $this->_binname;
    }

    /**
     * Get the binary executable
     *
     * @throws Exception
     * @return string
     */
    public function getBin()
    {
        $bin = $this->getBinPath() . $this->getBinFile();

        if (realpath($bin) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($bin,ENT_QUOTES).'")');
        if (file_exists($bin) === false)
            throw new Exception('WKPDF static executable "'.htmlspecialchars($bin,ENT_QUOTES).'" was not found');

        return $bin;
    }

    /**
     * Set absolute path where to store temporary HTML files
     *
     * @throws Exception
     * @param string $path
     * @return null
     */
    public function setTmpPath($path)
    {
        if (realpath($path) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($path,ENT_QUOTES).'")');

        $this->_tmpfilepath = realpath($path) . DIRECTORY_SEPARATOR;
        return;
    }

    /**
     * Get path where to store temporary HTML files
     *
     * @return string
     */
    public function getTmpPath()
    {
        return $this->_tmpfilepath;
    }

    /**
     * Set type of output mode
     *
     * @throws Exception
     * @param string $mode
     * @return null
     */
    public function setOutputMode($mode)
    {
        $this->_outputMode = $mode;
        return;
    }

    /**
     * Get type of output mode
     *
     * @return string
     */
    public function getOutputMode()
    {
        return $this->_outputMode;
    }

    /**
     * Set absolute path where to read HTML file
     *
     * @throws Exception
     * @param string $path
     * @return null
     */
    public function setHtmlPath($path)
    {
        if (realpath($path) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($path,ENT_QUOTES).'")');

        $this->_htmlfilepath = realpath($path) . DIRECTORY_SEPARATOR;
        return;
    }

    /**
     * Get path where to read HTML file
     *
     * @return string
     */
    public function getHtmlPath()
    {
        return $this->_htmlfilepath;
    }

    /**
     * Set filename holding HTML
     *
     * @return null
     */
    public function setHtmlFile($name)
    {
        $this->_htmlfilename = (string)$name;
        $this->_have_htmlfile = true;
        return;
    }

    /**
     * Get filename holding HTML
     *
     * @return string
     */
    public function getHtmlFile()
    {
        return $this->_htmlfilename;
    }

    /**
     * Get the path+filename holding HTML
     *
     * @throws Exception
     * @return string
     */
    public function getHtmlPathFile()
    {
        $file = $this->getHtmlPath() . $this->getHtmlFile();

        if (realpath($file) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($file,ENT_QUOTES).'")');
        if (file_exists($file) === false)
            throw new Exception('HTML file "'.htmlspecialchars($file,ENT_QUOTES).'" was not found');

        return $file;
    }

    /**
     * Set page orientation (default is portrait)
     *
     * @param string $orientation
     * @return null
     */
    public function setOrientation($orientation)
    {
        $this->_orientation = (string)$orientation;
        return;
    }

    /**
     * Returns page orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->_orientation;
    }

    /**
     * Set page/paper size (default is A4)
     * @param string $size
     * @return null
     */
    public function setPageSize($size)
    {
        $this->_pageSize = (string)$size;
        return;
    }

    /**
     * Returns page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Automatically generate a TOC (table of contents) or not (default is disabled)
     *
     * @param boolean $toc
     * @return Wkhtmltopdf
     */
    public function setTOC($toc = true)
    {
        $this->_toc = (boolean)$toc;
        return;
    }

    /**
     * Get value of whether automatic Table Of Contents generation is set
     *
     * @return boolean
     */
    public function getTOC()
    {
        return $this->_toc;
    }

    /**
     * Set the number of copies to make (default is 1)
     *
     * @param int $copies
     * @return null
     */
    public function setCopies($copies)
    {
        $this->_copies = (int)$copies;
        return;
    }

    /**
     * Get number of copies to make
     *
     * @return int
     */
    public function getCopies()
    {
        return $this->_copies;
    }

    /**
     * Whether to print in grayscale or not (default is off)
     *
     * @param boolean $mode
     * @return null
     */
    public function setGrayscale($mode)
    {
        $this->_grayscale = (boolean)$mode;
        return;
    }

    /**
     * Get if page will be printed in grayscale
     *
     * @return boolean
     */
    public function getGrayscale()
    {
        return $this->_grayscale;
    }

    /**
     * Set PDF title (default is HTML <title> of first document)
     *
     * @param string $title
     * @return null
     */
    public function setTitle($title)
    {
        $this->_title = (string)$title;
        return;
    }

    /**
     * Get PDF document title
     *
     * @throws Exception
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     *  Set header html (default is null)
     *
     * @param string $header
     * @return null
     */
    public function setHeaderHtml($header)
    {
        $this->_headerHtml = (string)$header;
        $this->_have_headerhtml = true;
        return;
    }

    /**
     * Get header html
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        return $this->_headerHtml;
    }

    /**
     *  Set footer html (default is null)
     *
     * @param string $footer
     * @return null
     */
    public function setFooterHtml($footer)
    {
        $this->_footerHtml = (string)$footer;
        $this->_have_footerhtml = true;
        return;
    }

    /**
     * Get footer html
     *
     * @return string
     */
    public function getFooterHtml()
    {
        return $this->_footerHtml;
    }

    /**
     * Set http username
     *
     * @param string $username
     * @return null
     */
    public function setUsername($username)
    {
        $this->_httpusername = (string)$username;
        return;
    }

    /**
     * Get http username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_httpusername;
    }

    /**
     * Set http password
     *
     * @param string $password
     * @return null
     */
    public function setPassword($password)
    {
        $this->_httppassword = (string)$password;
        return;
    }

    /**
     * Get http password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_httppassword;
    }

    /**
     *  Set any other WKTMLTOPDF options you need
     *
     * @param string $options
     * @return null
     */
    public function setOptions($options)
    {
        $this->_options = $options;
        return;
    }

    /**
     * Get any other WKTMLTOPDF options you need
     *
     * @return string
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set URL to render
     *
     * @param string $html
     * @return null
     */
    public function setHttpUrl($url)
    {
        $this->_httpurl = (string) $url;
        $this->_have_httpurl = true;
        return;
    }

    /**
     * Get URL to render
     *
     * @return string
     */
    public function getHttpUrl()
    {
        return $this->_httpurl;
    }

    /**
     * Set HTML content to render (replaces any previous content)
     *
     * @param string $html
     * @return null
     */
    public function setHtml($html)
    {
        $this->_html = (string)$html;
        $this->_have_html = true;
        return;
    }

    /**
     * Get current HTML content
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->_html;
    }

    /**
     * Create a temporary file & store the html content
     *
     * @return string   Full path to file
     */
    protected function _createFile($html)
    {
        $file = $this->_makeFilename();

        file_put_contents($file, $html);
        chmod($file, 0764);

        return $file;
    }

    /**
     * Create a temporary filename
     *
     * @throws Exception
     * @return string
     */
    protected function _makeFilename()
    {
        if (($path = $this->getTmpPath()) == '') {
            throw new Exception("Path to directory where to store files is not set");
        }
        if (realpath($path) === false)
            throw new Exception('Path must be absolute ("'.htmlspecialchars($path,ENT_QUOTES).'")');

        do {
            $file = mt_rand() . '.html';
        } while(file_exists($path.$file));

        return $path.$file;
    }

    /**
     * Delete a temporary file
     *
     * @param string fn Filename to delete (optional)
     * @return null
     */
    protected function _deleteFile($fn='')
    {
        if ($fn !== '') {
            unlink($fn);

        } else {
            // delete our temporary files
            if ($this->_have_html && $this->_tmphtmlfilename) {
                unlink($this->_tmphtmlfilename);
            }
            if ($this->_have_headerhtml && $this->_headerfilename) {
                unlink($this->_headerfilename);
            }
            if ($this->_have_footerhtml && $this->_footerfilename) {
                unlink($this->_footerfilename);
            }
        }

        return;
    }

    /**
     * Returns command to execute
     *
     * @param string    filename of input html
     * @return string
     */
    protected function _getCommand($in)
    {
        $command = '';
        $command = $this->getBin();

        $command .= " --orientation " . escapeshellarg($this->getOrientation());
        $command .= " --page-size " . escapeshellarg($this->getPageSize());
        $command .= ($this->getTOC()) ? " --toc" : "";
        $command .= ($this->getGrayscale()) ? " --grayscale" : "";
        $command .= ($this->getTitle()) ? ' --title "' . escapeshellarg($this->getTitle()) . '"' : "";
        $command .= ($this->getCopies() > 1) ? " --copies " . escapeshellarg($this->getCopies()) : "";
        $command .= (strlen($this->getPassword()) > 0) ? " --password " . escapeshellarg($this->getPassword()) . "" : "";
        $command .= (strlen($this->getUsername()) > 0) ? " --username " . escapeshellarg($this->getUsername()) . "" : "";
        $command .= $this->_have_headerhtml ? " --margin-top 20 --header-html " . escapeshellarg($this->_headerfilename) : "";
        $command .= $this->_have_footerhtml ? " --margin-bottom 20 --footer-html " . escapeshellarg($this->_footerfilename) : "";
        $command .= ($this->getOptions()) ? " {$this->getOptions()} " : "";

        /*
         * ignore some errors with some urls as recommended with this wkhtmltopdf error message:
         *      Error: Failed loading page <url> (sometimes it will work just to ignore this error with --load-error-handling ignore)
         */
        if ($this->getHttpUrl()) {
            // $command .= ' --load-error-handling ignore';
        }

        $command .= ' "'.$in.'" ';
        $command .= " -";

        return $command;
    }

    /**
     * Convert HTML to PDF.
     *
     * @todo use file cache
     *
     * @throws Exception
     * @return string
     */
    protected function _render()
    {
        if ($this->_have_httpurl)
        {                                                                                                             // source is url
            $input = $this->getHttpUrl();
        }
        // source is predefined disc file
        elseif ($this->_have_htmlfile)
        {
            $input = $this->getHtmlPathFile();
        }
        // source is html string
        elseif ($this->_have_html)
        {
            $input = $this->_tmphtmlfilename = $this->_createFile($this->getHtml());
        }
        else
        {
            throw new Exception("HTML content or source URL not set");
        }

        if ($this->_have_headerhtml)
        {
            $this->_headerfilename = $this->_createFile($this->getHeaderHtml());
        }

        if ($this->_have_footerhtml)
        {
            $this->_footerfilename = $this->_createFile($this->getFooterHtml());
        }

        $command = $this->_getCommand($input);

        $content = $this->_pipeExec($command);

        if($this->config->get('debug'))
        {
            dump(array(
                'input' => $input,
                'command' => $command,
                'content' => $content
            ));
        }

        if(preg_match('/error(?! ignored)/i', $content['stderr']))
		throw new Exception("System error <pre>" . $content['stderr'] . "</pre>");

        if (strlen($content['stdout']) === 0)
            throw new Exception("WKHTMLTOPDF didn't return any data");

        $data = $content['stdout'];

        return (isset($data)?$data:false);
    }

    /**
     * Executes the command :  Deprecated - use _pipeExec
     *
     * @param string $cmd   command to execute
     * @param string $input other input (not arguments)
     * @return array
     */
    protected function _exec($cmd, $input = "")
    {
        $result = array('stdout' => '', 'stderr' => '', 'return' => '');

        $proc = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $result['stdout'] = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $result['stderr'] = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $result['return'] = proc_close($proc);

        return $result;
    }

    /**
     * Advanced execution routine.
     *
     * @param string $cmd The command to execute.
     * @param string $input Any input not in arguments.
     * @return array An array of execution data; stdout, stderr and return "error" code.
     */
    private static function _pipeExec($cmd, $input='')
    {
        $pipes = array();
        $proc = proc_open($cmd, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, null, null, array('binary_pipes'=>true));
        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        // From http://php.net/manual/en/function.proc-open.php#89338
        $read_output = $read_error = false;
        $buffer_len  = $prev_buffer_len = 0;
        $ms          = 10;
        $stdout      = '';
        $read_output = true;
        $stderr      = '';
        $read_error  = true;
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        // dual reading of STDOUT and STDERR stops one full pipe blocking the other, because the external script is waiting
        while ($read_error != false or $read_output != false){
            if ($read_output != false){
                if(feof($pipes[1])){
                    fclose($pipes[1]);
                    $read_output = false;
                } else {
                    $str = fgets($pipes[1], 1024);
                    $len = strlen($str);
                    if ($len){
                        $stdout .= $str;
                        $buffer_len += $len;
                    }
                }
            }

            if ($read_error != false){
                if(feof($pipes[2])){
                    fclose($pipes[2]);
                    $read_error = false;
                } else {
                    $str = fgets($pipes[2], 1024);
                    $len = strlen($str);
                    if ($len){
                        $stderr .= $str;
                        $buffer_len += $len;
                    }
                }
            }

            if ($buffer_len > $prev_buffer_len){
                $prev_buffer_len = $buffer_len;
                $ms = 10;
            } else {
                usleep($ms * 1000); // sleep for $ms milliseconds
                if ($ms < 160){
                    $ms = $ms * 2;
                }
            }
        }

        $rtn = proc_close($proc);
        return array(
            'stdout' => $stdout,
            'stderr' => $stderr,
            'return' => $rtn
        );
    }

    /**
     * Return PDF with various options.
     *
     * @param int $mode                 How to output (constants from this same class - c.f. 'PDF get modes')
     * @param string $filename  The PDF's filename (usage depends on $mode)
     */
    public function output($mode, $filename='')
    {
        switch ($mode) {
            case self::MODE_DOWNLOAD:
                if (!headers_sent()) {
                    $result = $this->_render();
                    header("Content-Description: File Transfer");
                    header("Cache-Control: public; must-revalidate, max-age=0"); // HTTP/1.1
                    header("Pragme: public");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
                    header("Last-Modified: " . gmdate('D, d m Y H:i:s') . " GMT");
                    header("Content-Type: application/force-download");
                    header("Content-Type: application/octet-stream", false);
                    header("Content-Type: application/download", false);
                    header("Content-Type: application/pdf", false);
                    header('Content-Disposition: attachment; filename="' . basename($filename) .'";');
                    header("Content-Transfer-Encoding: binary");
                    header("Content-Length:" . strlen($result));
                    echo $result;
                    $this->_deleteFile();
                    exit();
                } else {
                    throw new Exception("Headers already sent");
                }
                break;
            case self::MODE_STRING:
                return $this->_render();
                break;
            case self::MODE_EMBEDDED:
                if (!headers_sent()) {
                    $result = $this->_render();
                    header("Content-type: application/pdf");
                    header("Cache-control: public, must-revalidate, max-age=0"); // HTTP/1.1
                    header("Pragme: public");
                    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
                    header("Last-Modified: " . gmdate('D, d m Y H:i:s') . " GMT");
                    header("Content-Length: " . strlen($result));
                    header('Content-Disposition: inline; filename="' . basename($filename) .'";');
                    echo $result;
                    $this->_deleteFile();
                    exit();
                } else {
                    throw new Exception("Headers already sent");
                }
                break;
            case self::MODE_SAVE:
                file_put_contents($filename, $this->_render());
                $this->_deleteFile();
                break;
            default:
                throw new Exception("Mode: " . $mode . " is not supported");
        }

        return TRUE;
    }
}
