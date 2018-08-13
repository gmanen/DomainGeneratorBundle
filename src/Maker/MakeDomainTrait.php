<?php

namespace WakeOnWeb\DomainGeneratorBundle\Maker;

/**
 * Trait MakeDomainTrait
 * @package WakeOnWeb\DomainGeneratorBundle\Maker
 */
trait MakeDomainTrait
{
    /** @var string */
    protected $projectDir;

    /**
     * DomainMakerTrait constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir, string $environment)
    {
        $this->projectDir = $projectDir;
    }
}
