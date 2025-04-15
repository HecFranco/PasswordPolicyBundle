<?php

declare(strict_types=1);

namespace HecFranco\PasswordPolicyBundle\DependencyInjection;


use Exception;
use HecFranco\PasswordPolicyBundle\EventListener\PasswordEntityListener;
use HecFranco\PasswordPolicyBundle\EventListener\PasswordExpiryListener;
use HecFranco\PasswordPolicyBundle\Exceptions\ConfigurationException;
use HecFranco\PasswordPolicyBundle\Model\HasPasswordPolicyInterface;
use HecFranco\PasswordPolicyBundle\Model\PasswordExpiryConfiguration;
use HecFranco\PasswordPolicyBundle\Service\PasswordExpiryService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PasswordPolicyExtension extends Extension
{

  /**
   * Loads a specific configuration.
   *
   * @throws Exception
   */
  public function load(array $configs, ContainerBuilder $containerBuilder): void
  {
    $yamlFileLoader = new YamlFileLoader(
      $containerBuilder,
      new FileLocator(__DIR__ . '/../Resources/config')
    );

    $yamlFileLoader->load('services.yml');

    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $this->addExpiryListener($containerBuilder, $config);

    $definition = $containerBuilder->getDefinition(PasswordExpiryService::class);

    foreach ($config['entities'] as $entityClass => $settings) {
      if (!class_exists($entityClass)) {
        throw new ConfigurationException(sprintf('Entity class %s not found', $entityClass));
      }

      $this->addEntityListener($containerBuilder, $entityClass, $settings);

      $passwordExpiryConfig = $containerBuilder->register(
        'password_expiry_configuration.' . $entityClass,
        PasswordExpiryConfiguration::class
      );
      $passwordExpiryConfig->setArguments([
        $entityClass,
        $settings['expiry_days'],
        $settings['notified_routes'],
        $settings['excluded_notified_routes'],
      ]);

      $definition->addMethodCall('addEntity', [$passwordExpiryConfig]);
    }
  }

  private function addExpiryListener(ContainerBuilder $containerBuilder, array $config): Definition
  {
    return $containerBuilder->autowire(PasswordExpiryListener::class)
      ->addTag('kernel.event_listener', [
        'event' => 'kernel.request',
        'priority' => $config['expiry_listener']['priority'],
      ])
      ->setArgument('$errorMessage', $config['expiry_listener']['error_msg']['text'])
      ->setArgument('$errorMessageType', $config['expiry_listener']['error_msg']['type']);
  }

  /**
   * @param $entityClass
   * @param $settings
   * @throws ConfigurationException
   */
  private function addEntityListener(
    ContainerBuilder $containerBuilder,
    string $entityClass,
    array $settings
  ): Definition {
    if (!is_a($entityClass, HasPasswordPolicyInterface::class, true)) {
      throw new ConfigurationException(sprintf(
        "Entity %s doesn't implement %s interface",
        $entityClass,
        HasPasswordPolicyInterface::class
      ));
    }

    $snakeClass = strtolower(str_replace('\\', '_', $entityClass));
    $definition = $containerBuilder->autowire(
      'hec_franco_password_policy.entity_listener.' . $snakeClass,
      PasswordEntityListener::class
    );

    $definition->addTag('doctrine.event_listener', ['event' => 'onFlush']);

    $definition->setArgument('$passwordField', $settings['password_field']);
    $definition->setArgument('$passwordHistoryField', $settings['password_history_field']);
    $definition->setArgument('$historyLimit', $settings['passwords_to_remember']);
    $definition->setArgument('$entityClass', $entityClass);

    return $definition;
  }

  public function getAlias(): string
  {
    return Configuration::ALIAS;
  }
}
