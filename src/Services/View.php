<?php namespace Ganymed\Services;


use Ganymed\Exceptions\ViewNotFoundException;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

class View {

    /**
     * Name of the view template file.
     *
     * @var String
     */
    private $template;

    /**
     * Name of the view layout file.
     *
     * @var String
     */
    private $layout;

    /**
     * Data for the layout/view.
     *
     * @var array
     */
    private $data = [];

    /**
     * Html file with resolved data variables.
     *
     * @var String
     */
    private $renderedView;

    /**
     * Path array supplied by app/config/paths.php.
     *
     * @var String
     */
    private $viewPath;

    function __construct($viewPath)
    {
        $this->viewPath = $viewPath;
    }

    public function withTemplate($template)
    {
        $this->template = str_replace('.', '/', $template);
        return $this;
    }

    /**
     * Set the layout file for the template/partial.
     *
     * @param $layout
     * @return $this
     */
    public function withLayout($layout)
    {
        $this->layout = str_replace('.', '/', $layout);
        return $this;
    }

    /**
     * Set the data for the template/partial.
     *
     * @param array $data
     * @return $this
     */
    public function withData(Array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Resolve layout and template files as well as the set data.
     *
     * @param $file
     * @param array $data
     * @return string
     * @throws ViewNotFoundException
     */
    private function make($file, Array $data)
    {
        if (!file_exists($file)) {
            throw new ViewNotFoundException($file);
        }

        extract($data);

        ob_start();
        include $file;
        $renderedView = ob_get_contents();
        ob_end_clean();

        return $renderedView;
    }

    /**
     * Render and returns the template/partial with the given layout if any.
     *
     * @return string
     * @throws ViewNotFoundException
     */
    public function render()
    {
        // Render template with supplied data.
        $templateFile = $this->viewPath . $this->template . '.php';
        $renderedTemplate = $this->make($templateFile, $this->data);

        if ($this->layout) {
            $layoutFile = $this->viewPath . $this->layout . '.php';
            // Add rendered template to supplied data.
            $this->data['renderedTemplate'] = $renderedTemplate;

            // Render layout with rendered template and supplied data.
            $this->renderedView = $this->make($layoutFile, $this->data);
        } else {
            $this->renderedView = $renderedTemplate;
        }

        echo $this->renderedView;
    }


}