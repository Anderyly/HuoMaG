<?php

class Error
{
    public function error($msg = '找不到指定的页面', $level = 'f')
    {
        header('HTTP/1.1 404 Not Found');
        header("status: 404 Not Found");
        if (APP_DEBUG) {
            halt($msg);
        } else {
            if ($level != 'f') {
                $this->controller->error($msg, 0, 0);
            } else {
                $file = PT_ROOT . '/' . $this->config->get('404file', '404.html');
                $this->log->write($msg);
                if (is_file($file)) {
                    $content = F($file);
                    $content = str_replace(array('{$sitename}', '{$siteurl}', '{$msg}'), array($this->config->get('sitename', 'PTCMS FrameWork'), $this->config->get('siteurl', PT_URL), $msg), $content);
                    exit($content);
                } else {
                    exit($msg . ' 页面出现错误，如需自定义此错误，请创建文件：' . $file);
                }
            }
        }
        exit;
    }
}