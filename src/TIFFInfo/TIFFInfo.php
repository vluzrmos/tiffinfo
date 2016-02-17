<?php

namespace Vluzrmos\TIFFInfo;

class TIFFInfo
{
    protected $input;
    protected $options = [];

    protected $output;
    protected $parsed = [];
    protected $cmd;

    public function __construct($input, array $options = [])
    {
        $this->input = $input;
        $this->options = $options;
    }

    /**
     * @param $input
     * @param array $options
     * @return $this
     */
    public function execTiffCmd($input, $options = [])
    {
    	if(empty($this->cmd)) {
    		$this->cmd = trim(trim(getenv('TIFFINFO_BIN')), '"') ?: 'tiffinfo';
    	}

        $input = escapeshellarg($input);
        $args = $this->parseOptions($options);
        $fullcmd = escapeshellcmd($this->cmd) . ' ' . implode(' ', $args) . ' ' . $input;

        exec($fullcmd, $output, $statusCode);

        if ($statusCode != 0) {
            throw new \InvalidArgumentException($output);
        }

        $this->output = $output;

        return $output;
    }

    protected function parseOptions($options)
    {
        return array_map(function ($value, $index) {
            if (is_numeric($index)) {
                return escapeshellarg($value);
            }

            if (substr($index, 0, 2) == '--') {
                return $index . '=' . escapeshellarg($value);
            }

            if (substr($index, 0, 1) == '-' && is_null($value)) {
                return $index;
            }

            return $index . ' ' . escapeshellarg($value);

        }, $options, array_keys($options));
    }

    protected function parsedOutput()
    {
        if (!empty($this->parsed)) {
            return $this->parsed;
        }

        if (empty($this->output)) {
            $this->execTiffCmd($this->input, $this->options);
        }

        $page = -1;

        foreach ($this->output as $line) {
            if (substr($line, 0, 4) == 'TIFF') {
                $page++;
                $this->parsed['pages'][$page]['Directory'] = $line;
                continue;
            }

            if (preg_match('/^\s.+/', $line)) {
                $pos = strpos($line, ':');
                $key = trim(substr($line, 0, $pos));
                $value = trim(substr($line, $pos + 1));

                $this->parsed['pages'][$page][$key] = $value;
            }
        }

        return $this->parsed;
    }

    /**
     * @return int
     */
    public function totalPages()
    {
        return count($this->pages());
    }

    /**
     * @return array
     */
    public function pages()
    {
        $output = $this->parsedOutput();

        if (isset($output['pages'])) {
            return $output['pages'];
        }

        return [];
    }

    /**
     * @param $page
     * @return array
     */
    public function page($page)
    {
        $output = $this->parsedOutput();

        if (isset($output['pages']) && isset($output['pages'][$page])) {
            return $output['pages'][$page];
        }

        return [];
    }

    /**
     * @return array
     */
    public function info()
    {
        return $this->parsedOutput();
    }

    /**
     * @return $this
     */
    public function refresh() {
    	$this->output = null;
    	$this->parsed = [];
    	$this->cmd = null;

    	return $this;
    }
}