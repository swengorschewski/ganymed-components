<?php namespace Ganymed\Templating;

use Ganymed\exceptions\ViewNotFoundException;

class Template {

    protected $viewPath;
    protected $fileName;
    protected $fileContents;
    protected $data = [];

    public function __construct($viewPath)
    {
        $this->viewPath = rtrim($viewPath, '/') . '/';
    }
    
    public function display()
    {
        return $this->fileContents;
    }

    public function render($fileName)
    {
        $this->parse($fileName);
        return $this;
    }

    private function parse($fileName)
    {
        $this->fileContents = $this->getFileContent($fileName);

        $this->parseIncludes();
        $this->parseSections();
        $this->parseExtends();
        $this->parseYields();
        $this->parseExpressions();
        $this->parseData();
    }

    private function getFileContent($fileName)
    {
        $fileName = $this->viewPath . str_replace("'", "", str_replace('.', '/', $fileName)) . '.php';
        if(is_file($fileName)) {
            return file_get_contents($fileName);
        } else {
            throw new ViewNotFoundException('Template File ' . $fileName . ' not found.');
        }
    }


    
    public function parseIncludes()
    {
    $includePattern = '/(?<=\@include\().*?(?=\))/';
    preg_match_all($includePattern, $this->fileContents, $includes);

        $includes = $includes[0];

        foreach($includes as $include) {
            $this->fileContents = str_replace(
                '@include(' . $include . ')',
                $this->getFileContent($include),
                $this->fileContents
            );
        }
    }

    public function parseSections()
    {
        $sectionPattern = "/@section\('([^']+)'\)([^@]+)/";
        preg_match_all($sectionPattern, $this->fileContents, $sections);
        $this->data = array_merge(array_combine($sections[1], array_map('trim', $sections[2])), $this->data);

        pd($this->data);
    }

    public function parseYields()
    {
        $yieldPattern = '/(?<=\@yield\().*?(?=\))/';
        preg_match_all($yieldPattern, $this->fileContents, $yields);

        $yields = $yields[0];

        foreach($yields as $yield) {
            $this->fileContents = str_replace(
                '@yield(' . $yield . ')',
                '{{ $' . str_replace("'", "", str_replace('"', '', $yield)) . ' }}',
                $this->fileContents
            );
        }
    }
    
    public function parseExtends()
    {
        $extendsPattern = '/(?<=\@extends\().*?(?=\))/';
        preg_match($extendsPattern, $this->fileContents, $extends);

        if(!empty($extends)) {
            $this->parse($extends[0]);
        }
    }

    private function data($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function parseExpressions()
    {
        $patterns = [
            '/@endforeach/' => '}',
            '/\\@foreach(.*)/' => 'foreach$1 {',

            '/@endif/' => '}',
            '/\\@if(.*)/' => 'if$1 {',
        ];

        foreach($patterns as $pattern => $replacement) {
            $this->fileContents = preg_replace($pattern, $replacement, $this->fileContents);
        }
    }

    public function parseData()
    {
        $pattern = '/{{(.*?)}}/';
        $replacement = '$1';
        $this->fileContents = preg_replace($pattern, $replacement, $this->fileContents);
    }

}