<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\gql\interfaces;

use craft\base\ElementInterface;
use craft\gql\base\InterfaceType;
use craft\gql\GqlEntityRegistry;
use craft\gql\TypeLoader;
use craft\gql\types\DateTime;
use craft\gql\types\generators\ElementType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InterfaceType as GqlInterfaceType;

/**
 * Class Element
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.3.0
 */
class Element extends InterfaceType
{
    /**
     * @inheritdoc
     */
    public static function getTypeGenerator(): string
    {
        return ElementType::class;
    }

    /**
     * @inheritdoc
     */
    public static function getType($fields = null): Type
    {
        if ($type = GqlEntityRegistry::getEntity(self::class)) {
            return $type;
        }

        $type = GqlEntityRegistry::createEntity(self::class, new GqlInterfaceType([
            'name' => static::getName(),
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'This is the interface implemented by all elements.',
            'resolveType' => function (ElementInterface $value) {
                return $value->getGqlTypeName();
            }
        ]));

        foreach (ElementType::generateTypes() as $typeName => $generatedType) {
            TypeLoader::registerType($typeName, function () use ($generatedType) { return $generatedType ;});
        }

        return $type;
    }

    /**
     * @inheritdoc
     */
    public static function getFieldDefinitions(): array
    {
        return array_merge(parent::getFieldDefinitions(), [
            'title' => [
                'name' => 'title',
                'type' => Type::string(),
                'description' => 'The element’s title.'
            ],
            'slug' => [
                'name' => 'slug',
                'type' => Type::string(),
                'description' => 'The element’s slug.'
            ],
            'uri' => [
                'name' => 'uri',
                'type' => Type::string(),
                'description' => 'The element’s URI.'
            ],
            'enabled' => [
                'name' => 'enabled',
                'type' => Type::boolean(),
                'description' => 'Whether the element is enabled or not.'
            ],
            'archived' => [
                'name' => 'archived',
                'type' => Type::boolean(),
                'description' => 'Whether the element is archived or not.'
            ],
            'siteId' => [
                'name' => 'siteId',
                'type' => Type::int(),
                'description' => 'The ID of the site the element is associated with.'
            ],
            'searchScore' => [
                'name' => 'searchScore',
                'type' => Type::string(),
                'description' => 'The element’s search score, if the `search` parameter was used when querying for the element.'
            ],
            'trashed' => [
                'name' => 'trashed',
                'type' => Type::boolean(),
                'description' => 'Whether the element has been soft-deleted or not.'
            ],
            'status' => [
                'name' => 'status',
                'type' => Type::string(),
                'description' => 'The element\'s status.'
            ],
            'dateCreated' => [
                'name' => 'dateCreated',
                'type' => DateTime::getType(),
                'description' => 'The date the element was created.'
            ],
            'dateUpdated' => [
                'name' => 'dateUpdated',
                'type' => DateTime::getType(),
                'description' => 'The date the element was last updated.'
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getName(): string
    {
        return 'ElementInterface';
    }
}
