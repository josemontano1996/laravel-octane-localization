<?php

namespace Illuminate\Support\Facades {
    use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterMacros;

    /**
     * @method static \Illuminate\Routing\RouteRegistrar localizedWithPrefix(callable|null $callback = null)
     * @method static \Illuminate\Routing\RouteRegistrar localizedWithoutPrefix(callable|null $callback = null)
     *
     * @see RegisterMacros
     */
    class Route {}
}

namespace Illuminate\Routing {
    use Josemontano1996\LaravelOctaneLocalization\Registrars\RegisterMacros;

    /**
     * @method \Illuminate\Routing\RouteRegistrar localizedWithoutPrefix(callable|null $callback = null)
     * @method \Illuminate\Routing\RouteRegistrar localizedWithPrefix(callable|null $callback = null)
     *
     * @see RegisterMacros
     */
    class Router {}
}
