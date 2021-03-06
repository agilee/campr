<?php

namespace AppBundle\Services;

use AppBundle\Entity\FileSystem;
use AppBundle\Repository\FileSystemRepository;
use Component\Resource\Model\FileSystemAwareInterface;

class FileSystemResolver
{
    /**
     * @var FileSystemRepository
     */
    private $repository;

    /**
     * FileSystemResolver constructor.
     *
     * @param FileSystemRepository $repository
     */
    public function __construct(FileSystemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param FileSystemAwareInterface $object
     *
     * @return FileSystem
     */
    public function resolve(FileSystemAwareInterface $object)
    {
        $fs = $object->getFileSystem();
        if (!$fs) {
            $fs = $this->getDefaultFileSystem();
        }

        return $fs;
    }

    /**
     * @return FileSystem
     */
    private function getDefaultFileSystem(): FileSystem
    {
        /** @var FileSystem $fs */
        $fs = $this
            ->repository
            ->findOneBy(
                [
                    'isDefault' => true,
                ]
            )
        ;

        return $fs;
    }
}
