<?php namespace Ganymed\Templating;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

use Ganymed\exceptions\ViewNotFoundException;

class View {

    protected $viewPath;
    protected $fileName;
    protected $fileContents;
    protected $data = [];

    public function __construct($viewPath)
    {
        $this->viewPath = rtrim($viewPath, '/') . '/';
    }
    
    public function render()
    {
        $this->parse();

        extract($this->data);

        ob_start();
        eval('?> ' . $this->fileContents . ' <?php ');
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public function renderPartial($string)
    {
        extract($this->data);

        ob_start();
        eval('?> ' . $string . ' <?php ');
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    public function make($fileName)
    {
        $this->fileContents = $this->getFileContent($fileName);
        return $this;
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

    private function parse()
    {
        $this->parseIncludes();
        $this->parseSections();
        $this->parseExtends();
        $this->parseYields();
        $this->fileContents = $this->parseExpressions($this->fileContents);
        $this->fileContents = $this->parseData($this->fileContents);
    }

    private function parseIncludes()
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

    private function parseSections()
    {
        $sectionPattern = "/@section\('([^']+)'\)(.*?)@endsection/s";
        preg_match_all($sectionPattern, $this->fileContents, $sections);
        $sections = array_combine($sections[1], array_map('trim', $sections[2]));

        foreach($sections as $key => $section) {
            $section = $this->parseExpressions($section);
            $section = $this->parseData($section);
            $sections[$key] = $this->renderPartial($section);
        }

        $this->setData($sections);
    }

    private function parseYields()
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

        if(!empty($yields)) {
            $this->parse();
        }
    }

    private function parseExtends()
    {
        $extendsPattern = '/(?<=\@extends\().*?(?=\))/';
        preg_match($extendsPattern, $this->fileContents, $extends);

        if(!empty($extends)) {
            $this->make($extends[0]);
        }
    }

    private function parseExpressions($content)
    {
        $patterns = [
            '/@endforeach/' => '<?php } ?>',
            '/\\@foreach(.*)/' => '<?php foreach$1 { ?>',

            '/@endif/' => '<?php } ?>',
            '/\\@if(.*)/' => '<?php if$1 { ?>',
        ];

        foreach($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    private function parseData($data)
    {
        return preg_replace('/{{(.*?)}}/', '<?php echo $1; ?>', $data);
    }

    private function setData($array)
    {
        $this->data = array_merge($array, $this->data);
    }

    public function with($array)
    {
        $this->setData($array);
        return $this;
    }

    public function getParsedView()
    {
        return $this->fileContents;
    }
}