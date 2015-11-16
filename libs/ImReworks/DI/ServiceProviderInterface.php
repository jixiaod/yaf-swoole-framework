<?php

namespace ImReworks\DI;

interface ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $di A container instance
     */
    public function register(Container $di);
}
