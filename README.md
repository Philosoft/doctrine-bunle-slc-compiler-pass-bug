Just do `composer install` and observe.

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
