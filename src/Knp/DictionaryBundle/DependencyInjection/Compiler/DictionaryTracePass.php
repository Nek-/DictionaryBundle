<?php

namespace Knp\DictionaryBundle\DependencyInjection\Compiler;

use Knp\DictionaryBundle\Dictionary\TraceableDictionaryInterface;
use Knp\DictionaryBundle\Dictionary\TraceableDictionaryTrait;
use Knp\DictionaryBundle\Exception\RuntimeException;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Factory;
use Nette\PhpGenerator\PhpNamespace;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Replace dictionary classes by traceable generated proxies.
 */
class DictionaryTracePass implements CompilerPassInterface
{
    const NAMESPACE = 'Proxies\\__DictionaryBundle__';
    /**
     * @var Factory
     */
    private $classFactory;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        // Do nothing if we are not in a debug environment
        if (false === $container->has('knp_dictionary.data_collector.dictionary_data_collector')) {
            return;
        }

        $cacheDirectory = $this->getCacheDirectory();

        $collector = new Reference('knp_dictionary.data_collector.dictionary_data_collector');
        $services = $container->findTaggedServiceIds(DictionaryRegistrationPass::TAG_DICTIONARY);

        foreach ($services as $id => $tags) {
            $md5Id = \md5($id);
            $serviceId = \sprintf('%s.%s.traceable', $id, $md5Id);
            $originalDictionaryReference = new Reference(\sprintf('%s.inner', $serviceId));
            $originalClass = $container->getDefinition($id)->getClass();

            // Generate the proxy
            $originalClassRef = new \ReflectionClass($originalClass);
            $namespace = new PhpNamespace(self::NAMESPACE . '\\' . $originalClassRef->getNamespaceName());
            $class = $namespace->addClass($originalClass . 'Traceable' . $md5Id);
            $class
                ->addImplement(TraceableDictionaryInterface::class)
                ->setExtends($originalClass)
                ->addTrait(TraceableDictionaryTrait::class)
            ;

            $proxyMethods = [];
            foreach ($originalClassRef->getMethods() as $method) {
                if (!$method->isPublic()) {
                    continue;
                }

                $proxyMethod = $this->getClassFactory()->fromMethodReflection($method);
                $proxyMethod->setBody('
$this->trace();
$args = func_get_args();
return parent::' . $method->getName() . '(...$args);');

                $proxyMethods[] = $proxyMethod;
            }
            $class->setMethods($proxyMethods);

            $this->saveClass($cacheDirectory, $namespace);

            // add it as service

            $traceable = new Definition();

            /**


            $traceable = new Definition('Knp\DictionaryBundle\Dictionary\TraceableDictionary', [$dictionary, $collector]);
            $traceable->setDecoratedService($id);
            //*/
            $container->setDefinition($serviceId, $traceable);
        }


    }

    private function getCacheDirectory(ContainerBuilder $container): string
    {
        $container->getParameter('kernel.cache_dir');
        if (empty($cacheDirectory)) {
            throw new RuntimeException('To enable trace on dictionary, the DictionaryBundle needs the variable "kernel.cache_dir".');
        }
        $cacheDirectory .= '/DictionaryBundle';

        if (!file_exists($cacheDirectory)) {
            mkdir($cacheDirectory, 0777, true);
        }

        return $cacheDirectory;
    }

    private function saveClass(string $cacheDir, PhpNamespace $namespace)
    {
        file_put_contents($cacheDir . '/' . $namespace->getClasses()[0]->getName() . '.php', (string) $namespace);
    }

    /**
     * @return Factory
     */
    private function getClassFactory()
    {
        if ($this->classFactory) {
            return $this->classFactory;
        }

        return $this->classFactory = new Factory();
    }
}
