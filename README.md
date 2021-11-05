Just do `composer install` and observe.

```
$ composer install
Installing dependencies from lock file (including require-dev)
Verifying lock file contents can be installed on current platform.
Package operations: 56 installs, 0 updates, 0 removals
  - Installing composer/package-versions-deprecated (1.11.99.4): Extracting archive
  - Installing symfony/flex (v1.17.2): Extracting archive
  - Installing symfony/polyfill-php80 (v1.23.1): Extracting archive
  - Installing symfony/runtime (v5.3.10): Extracting archive
  - Installing psr/cache (1.0.1): Extracting archive
  - Installing doctrine/lexer (1.2.1): Extracting archive
  - Installing doctrine/annotations (1.13.2): Extracting archive
  - Installing symfony/deprecation-contracts (v2.4.0): Extracting archive
  - Installing symfony/routing (v5.3.7): Extracting archive
  - Installing symfony/polyfill-mbstring (v1.23.1): Extracting archive
  - Installing symfony/polyfill-php73 (v1.23.0): Extracting archive
  - Installing symfony/http-foundation (v5.3.10): Extracting archive
  - Installing symfony/http-client-contracts (v2.4.0): Extracting archive
  - Installing psr/event-dispatcher (1.0.0): Extracting archive
  - Installing symfony/event-dispatcher-contracts (v2.4.0): Extracting archive
  - Installing symfony/event-dispatcher (v5.3.7): Extracting archive
  - Installing symfony/var-dumper (v5.3.10): Extracting archive
  - Installing psr/log (1.1.4): Extracting archive
  - Installing symfony/error-handler (v5.3.7): Extracting archive
  - Installing symfony/http-kernel (v5.3.10): Extracting archive
  - Installing symfony/finder (v5.3.7): Extracting archive
  - Installing symfony/filesystem (v5.3.4): Extracting archive
  - Installing psr/container (1.1.1): Extracting archive
  - Installing symfony/service-contracts (v2.4.0): Extracting archive
  - Installing symfony/dependency-injection (v5.3.10): Extracting archive
  - Installing symfony/polyfill-php81 (v1.23.0): Extracting archive
  - Installing symfony/config (v5.3.10): Extracting archive
  - Installing symfony/var-exporter (v5.3.8): Extracting archive
  - Installing symfony/cache-contracts (v2.4.0): Extracting archive
  - Installing symfony/cache (v5.3.10): Extracting archive
  - Installing symfony/framework-bundle (v5.3.10): Extracting archive
  - Installing symfony/stopwatch (v5.3.4): Extracting archive
  - Installing symfony/polyfill-intl-normalizer (v1.23.0): Extracting archive
  - Installing symfony/polyfill-intl-grapheme (v1.23.1): Extracting archive
  - Installing symfony/string (v5.3.10): Extracting archive
  - Installing symfony/console (v5.3.10): Extracting archive
  - Installing laminas/laminas-code (4.4.3): Extracting archive
  - Installing friendsofphp/proxy-manager-lts (v1.0.5): Extracting archive
  - Installing doctrine/event-manager (1.1.1): Extracting archive
  - Installing doctrine/deprecations (v0.5.3): Extracting archive
  - Installing doctrine/cache (2.1.1): Extracting archive
  - Installing doctrine/dbal (3.1.3): Extracting archive
  - Installing doctrine/migrations (3.3.0): Extracting archive
  - Installing doctrine/collections (1.6.8): Extracting archive
  - Installing doctrine/persistence (2.2.3): Extracting archive
  - Installing symfony/doctrine-bridge (v5.3.8): Extracting archive
  - Installing doctrine/sql-formatter (1.1.1): Extracting archive
  - Installing doctrine/doctrine-bundle (2.4.3): Extracting archive
  - Installing doctrine/doctrine-migrations-bundle (3.2.0): Extracting archive
  - Installing doctrine/instantiator (1.4.0): Extracting archive
  - Installing doctrine/inflector (2.0.4): Extracting archive
  - Installing doctrine/common (3.2.0): Extracting archive
  - Installing doctrine/orm (2.10.2): Extracting archive
  - Installing symfony/dotenv (v5.3.10): Extracting archive
  - Installing symfony/proxy-manager-bridge (v5.3.4): Extracting archive
  - Installing symfony/yaml (v5.3.6): Extracting archive
Generating optimized autoload files
composer/package-versions-deprecated: Generating version class...
composer/package-versions-deprecated: ...done generating version class
46 packages you are using are looking for funding.
Use the `composer fund` command to find out more!

Run composer recipes at any time to see the status of your Symfony recipes.

Executing script cache:clear [KO]
 [KO]
Script cache:clear returned with error code 1
!!
!!  In Definition.php line 321:
!!                                     
!!    The argument "1" doesn't exist.  
!!                                     
!!
!!
Script @auto-scripts was called via post-install-cmd
```

Problem stems from [this commit](https://github.com/doctrine/DoctrineBundle/commit/64234c75f49853e5619e98e9ed53493c3a3d85dc). Simple

```php
foreach ($factoryDefinition->getMethodCalls() as $factoryMethodCall) {
    if ($factoryMethodCall[0] !== 'setRegion') {
        continue;
    }

    try {
        $driverId = (string) $container->getDefinition($factoryMethodCall[1][0])->getArgument(1);
        if (! $container->hasAlias($driverId)) {
            continue;
        }
    
        $this->wrapIfNecessary($container, $driverId, (string) $container->getAlias($driverId), false);
    } catch (\Symfony\Component\DependencyInjection\Exception\OutOfBoundsException $e) {
        // ignore
    }
}
```

will take care of it. Not sure how correct this solution is though.

Diff:

```diff
@@ -65,12 +65,16 @@
                     continue;
                 }

-                $driverId = (string) $container->getDefinition($factoryMethodCall[1][0])->getArgument(1);
-                if (! $container->hasAlias($driverId)) {
-                    continue;
-                }
+                try {
+                    $driverId = (string) $container->getDefinition($factoryMethodCall[1][0])->getArgument(1);
+                    if (! $container->hasAlias($driverId)) {
+                        continue;
+                    }

-                $this->wrapIfNecessary($container, $driverId, (string) $container->getAlias($driverId), false);
+                    $this->wrapIfNecessary($container, $driverId, (string) $container->getAlias($driverId), false);
+                } catch (\Symfony\Component\DependencyInjection\Exception\OutOfBoundsException $e) {
+                    // ignore
+                }
             }
```
