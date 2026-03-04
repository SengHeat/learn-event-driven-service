<?php

namespace App\Providers;

use App\Messaging\Config\AwsMessagingConfig;
use App\Messaging\Config\KafkaMessagingConfig;
use App\Messaging\Config\RabbitMessagingConfig;
use App\Messaging\Config\RedisMessagingConfig;
use App\Messaging\Contracts\BrokerInterface;
use App\Messaging\Contracts\EventPublisherInterface;
use App\Messaging\Contracts\EventSubscriberInterface;
use App\Messaging\Enums\MessagingDriver;
use App\Messaging\Publishers\KafkaBrokerPublisher;
use App\Messaging\Publishers\RabbitBrokerPublisher;
use App\Messaging\Publishers\RedisBrokerPublisher;
use App\Messaging\Publishers\SnsBrokerPublisher;
use App\Messaging\Subscribers\KafkaBrokerSubscriber;
use App\Messaging\Subscribers\RabbitBrokerSubscriber;
use App\Messaging\Subscribers\RedisBrokerSubscriber;
use App\Messaging\Subscribers\SqsBrokerSubscriber;
use App\Repositories\Contracts\DomainEventRepositoryInterface;
use App\Repositories\DomainEventRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $driver = MessagingDriver::from(config('messaging.driver', 'redis'));

        match ($driver) {
            MessagingDriver::AWS    => $this->registerAws(),
            MessagingDriver::KAFKA  => $this->registerKafka(),
            MessagingDriver::RABBIT => $this->registerRabbit(),
            default                 => $this->registerRedis(),
        };

        $this->registerRepositories();
    }

    private function registerRedis(): void
    {
        $this->app->singleton(RedisMessagingConfig::class, fn() => RedisMessagingConfig::make());
        $this->app->bind(EventPublisherInterface::class,  RedisBrokerPublisher::class);
        $this->app->bind(EventSubscriberInterface::class, RedisBrokerSubscriber::class);
        $this->app->bind(BrokerInterface::class,          RedisBrokerSubscriber::class);
    }

    private function registerAws(): void
    {
        $this->app->singleton(AwsMessagingConfig::class, fn() => AwsMessagingConfig::make());

        // ✅ Bind SqsClient
        $this->app->singleton(\Aws\Sqs\SqsClient::class, function () {
            $config = AwsMessagingConfig::make();
            return new \Aws\Sqs\SqsClient([
                'region'      => $config->region,
                'version'     => 'latest',
                'endpoint'    => $config->endpoint,
                'credentials' => [
                    'key'    => $config->key,
                    'secret' => $config->secret,
                ],
            ]);
        });

        // ✅ Bind SnsClient
        $this->app->singleton(\Aws\Sns\SnsClient::class, function () {
            $config = AwsMessagingConfig::make();
            return new \Aws\Sns\SnsClient([
                'region'      => $config->region,
                'version'     => 'latest',
                'endpoint'    => $config->endpoint,
                'credentials' => [
                    'key'    => $config->key,
                    'secret' => $config->secret,
                ],
            ]);
        });

        $this->app->bind(EventPublisherInterface::class,  SnsBrokerPublisher::class);
        $this->app->bind(EventSubscriberInterface::class, SqsBrokerSubscriber::class);
        $this->app->bind(BrokerInterface::class,          SqsBrokerSubscriber::class);
    }

    private function registerKafka(): void
    {
        $this->app->singleton(KafkaMessagingConfig::class, fn() => KafkaMessagingConfig::make());
        $this->app->bind(EventPublisherInterface::class,  KafkaBrokerPublisher::class);
        $this->app->bind(EventSubscriberInterface::class, KafkaBrokerSubscriber::class);
        $this->app->bind(BrokerInterface::class,          KafkaBrokerSubscriber::class);
    }

    private function registerRabbit(): void
    {
        $this->app->singleton(RabbitMessagingConfig::class, fn() => RabbitMessagingConfig::make());
        $this->app->bind(EventPublisherInterface::class,  RabbitBrokerPublisher::class);
        $this->app->bind(EventSubscriberInterface::class, RabbitBrokerSubscriber::class);
        $this->app->bind(BrokerInterface::class,          RabbitBrokerSubscriber::class);
    }

    private function registerRepositories(): void
    {
        $this->app->bind(DomainEventRepositoryInterface::class, DomainEventRepository::class);
    }

    public function boot(): void {}
}
