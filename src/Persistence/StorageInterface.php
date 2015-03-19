<?php namespace Ganymed\Persistence;

/*
 * This file is part of the Ganymed Package.
 *
 * (c) Swen Gorschewski <swen.gorschewski@gmail.com>
 *
 * The Package is distributed under the MIT License
 */

interface StorageInterface {

    public function __call($name, $value);

    public function getAll();

    public function update($model);

    public function save($model);

    public function delete($id);

}