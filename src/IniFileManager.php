<?php

/**
 * Ini File Manager
 *
 * @copyleft 2020 - Wanderlei Santana
 * @author Wanderlei Santana <sans.pds@gmail.com>
 * @category FILES
 * @package Data Manager
 * @since 202006022349
 */

require __DIR__ . "/FileWriter.php";

class IniFileManager
{
    /**
     * File Path and Name
     *
     * @var string
     */
    protected $_file = null;
    
    /**
     * Data Config from File
     *
     * @var Array
     */
    protected $_data = null;
    
    /**
     * Construct
     *
     * @param string $file
     */
    public function __construct($file = null)
    {
        $this->loadFile($file);
    }
    
    /**
     * Load Config data from Ini File
     *
     * @param string $file
     *
     * @return $this
     *
     * @throws Exception
     */
    public function loadFile($file)
    {
        if (null == $file) {
            return $this;
        }
     
        if (!file_exists($file)) {
            throw new Exception("File '{$file}' does not exist.");
        }
        
        $this->setFile($file)->_parseFile();
        
        return $this;
    }
    
    /**
     * Set Ini File Path
     *
     * @param string $filepath
     *
     * @return $this
     */
    public function setFile($filepath)
    {
        $this->_file = $filepath;
        return $this;
    }
    
    /**
     * Return Loaded File Path and name
     *
     * @return string
     */
    public function getFile()
    {
        return $this->_file;
    }
    
    /**
     * Parse data from ini file
     *
     * @return $this
     */
    protected function _parseFile()
    {
        $configs = parse_ini_file($this->getFile(), true);
        
        foreach ($configs as $category => $properties) {    

            $this->addCategory($category);
            foreach ($properties as $item => $value) {
                $this->addItem($category, $item, $value);
            }
        }

        return $this;
    }

    /**
     * Add new Category to ini File
     *
     * @param string $category
     *
     * @return $this
     */
    public function addCategory($category = null)
    {
        if (null == $category) {
            return $this;
        }
        
        if ($this->_categoryExists($category)) {
            return $this;
        }
        
        $this->_data[strtolower(trim($category))] = array();

        return $this;
    }
    
    /**
     * Remove Category from ini file
     *
     * @param string $category
     *
     * @return $this
     */
    public function removeCategory($category)
    {
        $key = strtolower(trim($category));
        
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        
        return $this;
    }
    
    /**
     * Check if category exists
     *
     * @param string $categoryName
     *
     * @return bool
     */
    protected function _categoryExists($categoryName)
    {
        return isset($this->_data[strtolower(trim($categoryName))]);
    }
    
    /**
     * Add new Item
     *
     * @param string $category
     * @param string $item
     * @param mixed  $value
     * 
     * @return $this
     */
    public function addItem($category, $item, $value = null)
    {
        $this->setItem($category, $item, $value);
        return $this;
    }
    
    /**
     * Set Item value
     *
     * @param string $category
     * @param string $item
     * @param mixed $value
     *
     * @return $this
     */
    public function setItem($category, $item, $value = null)
    {
        $cat = strtolower(trim($category));
        if ($this->_categoryExists($cat)) {
            $this->addCategory($cat);
        }
        
        $property = strtolower(trim($item));
        $this->_data[$cat][$property] = $value;

        return $this;
    }
    
    /**
     * Get item from category
     *
     * @param type $category
     * @param type $item
     *
     * @return type
     */
    public function getItem($category, $item)
    {
        return $this->_data[$category][$item];
    }
    
    /**
     * Unset item from category
     *
     * @param string $category
     * @param string $item
     *
     * @return $this
     */
    public function removeItem($category, $item)
    {
        $cat = strtolower($category);
        $it = strtolower($item);
        
        if (isset($this->_data[$cat][$it])) {
            unset($this->_data[$cat][$it]);
        }

        return $this;
    }
    
    /**
     * Persist data to File
     *
     * @param string $file - filepath and name
     * 
     * @return $this
     */
    public function save($file = null)
    {
        if (null != $file) {
            $this->_createFile($file)->setFile($file);
        }

        $lines = file($this->getFile(), FILE_IGNORE_NEW_LINES);

        return empty($lines)
            ? $this->_insertDataIntoFile() 
            : $this->_updateDataIntoFile($lines);
    }
    
    /**
     * Create new empty File
     *
     * @param string $filepath
     *
     * @return $this
     */
    protected function _createFile($filepath)
    {
        $handler = fopen($filepath, "w");
        fwrite($handler, "");
        fclose($handler);
        
        return $this;
    }
    
    /**
     * Save data into a New File
     *
     * @return $this
     */
    protected function _insertDataIntoFile()
    {
        if (empty($this->_data)) {
            return $this;
        }

        $newIniFileText = "";
        $lineBreak = PHP_EOL;
        
        foreach ($this->_data as $categoria => $items) {
            if (!is_array($items)) {
                $newIniFileText .= "$categoria = " . (is_numeric($items) ? $items : '"'.$items.'"') . $lineBreak;
                continue;
            }
            
            $newIniFileText .= $lineBreak . "[$categoria]" . $lineBreak;
            foreach ($items as $skey => $sval) {
                $newIniFileText .= "{$skey} = " . (is_numeric($sval) ? $sval : '"'.$sval.'"') . $lineBreak;
            }
        }
 
        FileWriter::rewrite($this->getFile(), $newIniFileText);

        return $this;
    }
    
    /**
     * Update data into a File
     *
     * @return $this
     */
    protected function _updateDataIntoFile($lines)
    {
        $array = $this->_data;
        
        $newIniFileText = "";
        $category = '';
        $lineBreak = PHP_EOL;
        
        foreach ($lines as $line) {
            $row = trim($line);
    
            // is a empty line?
            if (empty($row)) {
                $newIniFileText .= $lineBreak;
                continue;
            }
    
            // is a comment ?
            if ($row[0] == ";" || $row[0] == "#") {
                $newIniFileText .= $line.$lineBreak;
                continue;
            }
    
            // is a Category?
            if ($row[0] == "[") {
                $newIniFileText .= $line.$lineBreak;
                
                if (isset($array[$category])) {
                    unset($array[$category]);
                }
                
                $category = strtolower(trim(str_replace(array('[', ']'), '', $row)));
                continue;
            }
    
            // is config!
            list($key) = explode("=", $row);
    
            // Data was removed from the Array? Remove from the file too.
            if (!isset($array[$category][trim($key)])) {
                continue;
            }
            
            $val = $array[$category][trim($key)];
            $newIniFileText .= trim($key) . " = " . (is_numeric($val) ? $val : "'{$val}'") . $lineBreak;
            
            // remove from array
            unset($array[$category][trim($key)]);
        }
    
        // reseta a ultima categoria lida antes de acabar o arquivo
        if (isset($array[$category])) {
            unset($array[$category]);
        }
    
        // adicionando ao arquivo categorias que nao exisitam antes.
        if (!empty($array)) {
            foreach ($array as $key => $val) {
                if (!is_array($val)) {
                    $newIniFileText .= "$key = " . (is_numeric($val) ? $val : '"'.$val.'"') . $lineBreak;
                }
                
                $newIniFileText .= $lineBreak . "[$key]" . $lineBreak;
                foreach ($val as $skey => $sval) {
                    $newIniFileText .= "{$skey} = " . (is_numeric($sval) ? $sval : '"'.$sval.'"') . $lineBreak;
                }
            }
        }
    
        FileWriter::rewrite($this->getFile(), $newIniFileText);
        
        return $this;
    }
}
