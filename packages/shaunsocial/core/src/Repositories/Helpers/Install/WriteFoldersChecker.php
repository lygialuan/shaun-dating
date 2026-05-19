<?php


namespace Packages\ShaunSocial\Core\Repositories\Helpers\Install;

class WriteFoldersChecker
{
    /**
     * @var array
     */
    protected $results = [];

    /**
     * Set the result array permissions and errors.
     *
     * @return mixed
     */
    public function __construct()
    {
        $this->results['folders'] = [];

        $this->results['errors'] = null;
    }

    /**
     * Check for the folders permissions.
     *
     * @param  array  $folders
     * @return array
     */
    public function check(array $folders)
    {
        foreach ($folders as $folder) {
            if (! ($this->getPermission($folder))) {
                $this->addFileAndSetErrors($folder, false);
            } else {
                $this->addFile($folder, true);
            }
        }

        return $this->results;
    }

    /**
     * Get a folder permission.
     *
     * @param $folder
     * @return string
     */
    private function getPermission($folder)
    {
        return is_writable(base_path($folder));
    }

    /**
     * Add the file to the list of results.
     *
     * @param $folder
     * @param $permission
     * @param $isSet
     */
    private function addFile($folder, $isSet)
    {
        array_push($this->results['folders'], [
            'folder' => $folder,
            'isSet' => $isSet,
        ]);
    }

    /**
     * Add the file and set the errors.
     *
     * @param $folder
     * @param $permission
     * @param $isSet
     */
    private function addFileAndSetErrors($folder, $isSet)
    {
        $this->addFile($folder, $isSet);

        $this->results['errors'] = true;
    }
}
