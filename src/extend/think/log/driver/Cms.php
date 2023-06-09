<?php

namespace think\log\driver;

use think\log\driver\File;

class Cms extends File
{

    const PREFIX = 'cms';

    /** @inheritDoc */
    protected function getMasterLogFile(): string
    {
        if ($this->config['max_files']) {
            $files = glob($this->config['path'] . '*.log');

            try {
                if (count($files) > $this->config['max_files']) {
                    unlink($files[0]);
                }
            } catch (\Exception $e) {
            }
        }

        $cli = PHP_SAPI == 'cli' ? '_cli' : '';

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';

            $destination = $this->config['path'] . $name . $cli . '.log';
        } else {
            if ($this->config['max_files']) {
                $filename = date('Ymd') . $cli . '.log';
            } else {
                $filename = date('Ymd') . DIRECTORY_SEPARATOR . Cms::PREFIX . $cli . '.log';
                // $filename = date('Ym') . DIRECTORY_SEPARATOR . date('d') . $cli . '.log';
            }

            $destination = $this->config['path'] . $filename;
        }

        return $destination;
    }

    /** @inheritDoc */
    protected function getApartLevelFile($path, $type): string
    {
        $cli = PHP_SAPI == 'cli' ? '_cli' : '';

        if ($this->config['single']) {
            $name = is_string($this->config['single']) ? $this->config['single'] : 'single';
        } elseif ($this->config['max_files']) {
            $name = date('Ymd');
        } else {
            //$name = date('d');
            $name = '';
        }

        return $path . DIRECTORY_SEPARATOR . ('' == $name ? '' : $name . '_') . $type . $cli . '.log';
    }
}
