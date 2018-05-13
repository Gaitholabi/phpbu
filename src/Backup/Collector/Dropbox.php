<?php
namespace phpbu\App\Backup\Collector;

use Kunnu\Dropbox\Dropbox as DropboxApi;
use phpbu\App\Backup\Collector;
use phpbu\App\Backup\Path;
use phpbu\App\Backup\Target;

/**
 * Dropbox class.
 *
 * @package    phpbu
 * @subpackage Backup
 * @author     Sebastian Feldmann <sebastian@phpbu.de>
 * @author     Vitaly Baev <hello@vitalybaev.ru>
 * @copyright  Sebastian Feldmann <sebastian@phpbu.de>
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link       http://phpbu.de/
 * @since      Class available since Release 5.1.0
 */
class Dropbox extends Collector
{
    /**
     * @var DropboxApi
     */
    protected $client;

    /**
     * Dropbox remote path
     *
     * @var Path
     */
    protected $path;

    /**
     * OpenStack constructor.
     *
     * @param \phpbu\App\Backup\Target $target
     * @param DropboxApi               $client
     * @param string                   $path
     * @param int                      $time
     */
    public function __construct(Target $target, DropboxApi $client, string $path, int $time)
    {
        $this->client = $client;
        $this->path   = new Path($path, $time, false, true);
        $this->setUp($target);
    }

    /**
     * Get all created backups.
     *
     * @return \phpbu\App\Backup\File[]
     */
    public function getBackupFiles() : array
    {
        $items = $this->client->listFolder($this->path->getPathThatIsNotChanging(), [
            'limit' => 100,
            'recursive' => true,
        ]);
        foreach ($items->getItems() as $item) {
            // skip directories
            if ($item instanceof \Kunnu\Dropbox\Models\FolderMetadata) {
                continue;
            }
            /** @var \Kunnu\Dropbox\Models\FileMetadata $item */
            // skip currently created backup
            if ($item->getPathDisplay() == $this->path->getPath() . $this->target->getFilename()) {
                continue;
            }
            if ($this->isFileMatch($item->getPathDisplay())) {
                $this->files[] = new \phpbu\App\Backup\File\Dropbox($this->client, $item);
            }
        }

        return $this->files;
    }
}
