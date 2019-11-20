<?php

namespace EventEspresso\core\domain\services\graphql\mutators;

use EEM_Ticket;
use EE_Ticket;
use EventEspresso\core\domain\services\graphql\types\Ticket;
use EventEspresso\core\domain\services\graphql\data\mutations\TicketMutation;

use EE_Error;
use InvalidArgumentException;
use ReflectionException;
use EventEspresso\core\exceptions\InvalidDataTypeException;
use EventEspresso\core\exceptions\InvalidInterfaceException;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use GraphQL\Error\UserError;

class TicketUpdate
{

    /**
     * Defines the mutation data modification closure.
     *
     * @param EEM_Ticket $model
     * @param Ticket     $type
     * @return callable
     */
    public static function mutateAndGetPayload(EEM_Ticket $model, Ticket $type)
    {
        /**
         * Updates an entity.
         *
         * @param array       $input   The input for the mutation
         * @param AppContext  $context The AppContext passed down to all resolvers
         * @param ResolveInfo $info    The ResolveInfo passed down to all resolvers
         * @return array
         * @throws UserError
         * @throws ReflectionException
         * @throws InvalidArgumentException
         * @throws InvalidInterfaceException
         * @throws InvalidDataTypeException
         * @throws EE_Error
         */
        return static function ($input, AppContext $context, ResolveInfo $info) use ($model, $type) {
            /**
             * Stop now if a user isn't allowed to create a datetime.
             */
            if (! current_user_can('ee_edit_events')) {
                // translators: the %1$s is the name of the object being mutated
                throw new UserError(
                    sprintf(__('Sorry, you are not allowed to edit %1$s', 'event_espresso'), $type->name())
                );
            }

            $id = ! empty($input['id']) ? absint($input['id']) : 0;
            $entity = null;

            if ($id) {
                $entity = $model->get_one_by_ID($id);
            }

            /**
             * If there's no existing datetime, throw an exception
             */
            if (! $id || ! ($entity instanceof EE_Ticket)) {
                // translators: the placeholder is the name of the type being updated
                throw new UserError(
                    sprintf(__('No %1$s could be found to update', 'event_espresso'), $type->name())
                );
            }

            $datetimes = [];

            $args = TicketMutation::prepareFields($input);

            if (isset($args['datetimes'])) {
                $datetimes = $args['datetimes'];
                unset($args['datetimes']);
            }

            // Update the entity
            $result = $entity->save($args);

            if (empty($result)) {
                throw new UserError(__('The object failed to update but no error was provided', 'event_espresso'));
            }

            if (! empty($datetimes)) {
                TicketMutation::setRelatedDatetimes($entity, $datetimes);
            }

            return [
                'id' => $id,
            ];
        };
    }
}