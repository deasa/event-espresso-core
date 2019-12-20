import { useApolloClient } from '@apollo/react-hooks';
import { propOr } from 'ramda';
import useCacheRehydrationData from './useCacheRehydrationData';
import useRelations from '../../../../application/services/apollo/relations/useRelations';
import useStatus from '../../../../application/services/apollo/status/useStatus';
import useEventId from './events/useEventId';
import { queries } from './';

const { GET_TICKETS, GET_DATETIMES, GET_PRICE_TYPES, GET_PRICES } = queries;

const useCacheRehydration = () => {
	const client = useApolloClient();
	const eventId = useEventId();
	const { setData } = useRelations();
	const {
		datetimes: espressoDatetimes,
		tickets: espressoTickets,
		prices: espressoPrices,
		priceTypes: espressoPriceTypes,
		relations,
	} = useCacheRehydrationData();
	const { isLoaded } = useStatus();

	if (isLoaded('priceTypes')) {
		return;
	}

	Object.entries({ priceTypes: espressoPriceTypes, datetimes: espressoDatetimes, tickets: espressoTickets }).forEach(
		([entityType, entities]) => {
			let nodes = propOr([], 'nodes', entities);

			if (!nodes.length) return;

			if (entityType === 'priceTypes') {
				client.writeQuery({
					query: GET_PRICE_TYPES,
					data: {
						espressoPriceTypes,
					},
				});
			}

			if (entityType === 'datetimes') {
				const dateTimeNodes = propOr([], 'nodes', espressoDatetimes);
				const datetimeIn = dateTimeNodes.map(({ id }) => id);

				client.writeQuery({
					query: GET_DATETIMES,
					variables: {
						where: {
							eventId,
						},
					},
					data: {
						espressoDatetimes,
					},
				});

				if (datetimeIn.length) {
					client.writeQuery({
						query: GET_TICKETS,
						variables: {
							where: {
								datetimeIn,
							},
						},
						data: {
							espressoTickets,
						},
					});
				}
			}

			if (entityType === 'tickets') {
				const ticketNodes = propOr([], 'nodes', espressoTickets);
				const ticketIn = ticketNodes.map(({ id }) => id);

				if (ticketIn.length) {
					client.writeQuery({
						query: GET_PRICES,
						variables: {
							where: {
								ticketIn,
							},
						},
						data: {
							espressoPrices,
						},
					});
				}
			}
		}
	);

	setData(relations);
};

export default useCacheRehydration;
