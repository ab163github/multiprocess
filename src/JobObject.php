<?php

/*
 * This file is part of PHP CS Fixer.
 * (c) kcloze <pei.greet@qq.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kcloze\MultiProcess;

class JobObject
{
    public $topic    ='';
    public $jobClass ='';
    public $jobMethod='';
    public $jobParams='';

    public function __construct($topic, $jobClass, $jobMethod, $jobParams)
    {
        $this->topic    =$topic;
        $this->jobClass =$jobClass;
        $this->jobMethod=$jobMethod;
        $this->jobParams=$jobParams;
    }
}
