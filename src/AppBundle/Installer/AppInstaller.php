<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace AppBundle\Installer;

use EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller;
use EzSystems\PlatformInstallerBundle\Installer\Installer;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Install data from ezplatform.com.
 *
 * Class AppInstaller
 */
class AppInstaller extends DbBasedInstaller implements Installer
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $tmpFolder;

    public function importSchema()
    {
        return;
    }

    public function importData()
    {
        $this->init();
        $this->output->writeln('<comment>Import database...</comment>' . PHP_EOL);

        $progress = new ProgressBar($this->output, 4);
        $progress->setFormat('verbose');

        $this->output->writeln('<info>Downloading sql dump...</info>');
        $progress->advance();

        $resource = fopen($this->tmpFolder . DIRECTORY_SEPARATOR . 'ezplatform_page_db.sql.bz2', 'w');
        $stream = \GuzzleHttp\Psr7\stream_for($resource);
        $this->client->request('GET', '/dumps/ezplatform_page_db.sql.bz2', ['save_to' => $stream]);

        $this->output->writeln(PHP_EOL . '<info>Extracting sql dump...</info>');
        $progress->advance();
        $process = new Process(sprintf('bzip2 -d  %sezplatform_page_db.sql.bz2', $this->tmpFolder . DIRECTORY_SEPARATOR));
        $process->run();

        $this->output->writeln(PHP_EOL . '<info>Running sql dump...</info>');
        $progress->advance();
        $this->output->writeln('');
        $this->runQueriesFromFile($this->tmpFolder . DIRECTORY_SEPARATOR . 'ezplatform_page_db.sql');

        $progress->finish();
        $this->output->writeln('');
        $this->output->writeln(PHP_EOL . '<comment>Import database done.</comment>' . PHP_EOL);
    }

    public function createConfiguration()
    {
        // configuration is included in ezplatform.yml file
    }

    public function importBinaries()
    {
        $this->init();
        $this->output->writeln('<comment>Import Storage...</comment>' . PHP_EOL);

        $progress = new ProgressBar($this->output, 4);
        $progress->setFormat('verbose');
        $this->output->writeln('<info>Downloading storage directory contents...</info>');

        $resource = fopen($this->tmpFolder . DIRECTORY_SEPARATOR . 'ezplatform_page_storage.tar.bz2', 'w');
        $stream = \GuzzleHttp\Psr7\stream_for($resource);
        $this->client->request('GET', '/dumps/ezplatform_page_storage.tar.bz2', ['save_to' => $stream]);
        $progress->advance();

        $this->output->writeln(PHP_EOL . '<info>Extracting storage directory contents...</info>');
        $fs = new Filesystem();
        $fs->mkdir($this->tmpFolder . DIRECTORY_SEPARATOR . 'ezplatform_page_storage');
        $process = new Process(sprintf('tar -xjf %1$sezplatform_page_storage.tar.bz2 -C %1$sezplatform_page_storage', $this->tmpFolder . DIRECTORY_SEPARATOR));
        $process->setTimeout(0);
        $process->run();
        $progress->advance();

        $this->output->writeln(PHP_EOL . '<info>Moving storage directory contents...</info>');
        $fs->mkdir('web/var/site/storage');
        $fs->mirror(
            $this->tmpFolder . DIRECTORY_SEPARATOR . 'ezplatform_page_storage',
            'web/var/site/storage'
        );
        $progress->advance();

        $this->output->writeln(PHP_EOL . '<info>Cleaning tmp files...</info>');
        $fs->remove($this->tmpFolder . DIRECTORY_SEPARATOR);

        $progress->finish();

        $this->output->writeln('');
        $this->output->writeln(PHP_EOL . '<comment>Import Storage done.</comment>' . PHP_EOL);
    }

    private function init()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://ezplatform.com']);
        $this->tmpFolder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ezplatform-installer';
        $fs = new Filesystem();
        $fs->mkdir($this->tmpFolder);
    }
}
