<?php

// PHPUnit 6 introduced a breaking change that removed PHPUnit_Framework_TestCase as a base class,
// and replaced it with \PHPUnit\Framework\TestCase
// 为满足兼容性，当使用 PHPUnit 高版本时，对新的 TestCase 创建别名
if (!class_exists('\PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

// 根据 PHPUnit 版本加载对应的的基础类，并设置别名。
// 如果有新的 setUp 等动作，可以新增 Base 类，在下面设置别名并在具体的测试类中使用。
if (class_exists('PHPUnit\Runner\Version') && version_compare(PHPUnit\Runner\Version::id(), '8.0.0', '>=')) {
    require_once __DIR__ . "/PHPUnit8Base.php";
    if (!class_exists('PHPUnitBase')) {
        class_alias('PHPUnit8Base', 'PHPUnitBase');
    }
} else {
    require_once __DIR__ . "/PHPUnit7Base.php";
    if (!class_exists('PHPUnitBase')) {
        class_alias('PHPUnit7Base', 'PHPUnitBase');
    }
}