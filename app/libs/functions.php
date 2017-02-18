<?php
/**
 * Obecné funkce , které fungují napříč celým projektem
 * Created by PhpStorm.
 * User: Jarda
 * Date: 16.01.2017
 * Time: 10:00
 */

namespace FrontModule;

function bd($var, $title = NULL) {
    \Tracy\Debugger::barDump($var, $title);
}