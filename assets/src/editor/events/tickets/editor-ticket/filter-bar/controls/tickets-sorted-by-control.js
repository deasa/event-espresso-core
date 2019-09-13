/**
 * External imports
 */
import PropTypes from 'prop-types';
import { SelectControl } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { __ } from '@eventespresso/i18n';

/**
 * filter for controlling the sorting of a list of Tickets
 *
 * @param {string} ticketsSortedBy
 * @param {Function} setTicketsSortedBy
 * @return {Object} rendered control
 */
const TicketsSortedByControl = ( { ticketsSortedBy, setTicketsSortedBy } ) => {
	return useMemo( () => {
		return (
			<SelectControl
				label={ __( 'sort', 'event_espresso' ) }
				className="ee-ticket-list-filter-bar-order-select"
				value={ ticketsSortedBy }
				options={ [
					{
						value: 'chronologically',
						label: __(
							'chronologically',
							'event_espresso'
						),
					},
					{
						value: 'by-name',
						label: __( 'by ticket name', 'event_espresso' ),
					},
					{
						value: 'by-id',
						label: __( 'by ticket ID', 'event_espresso' ),
					},
					{
						value: 'by-order',
						label: __(
							'by custom order',
							'event_espresso'
						),
					},
				] }
				onChange={ setTicketsSortedBy }
			/>
		);
	}, [ ticketsSortedBy, setTicketsSortedBy ] );
};

TicketsSortedByControl.propTypes = {
	ticketsSortedBy: PropTypes.string,
	setTicketsSortedBy: PropTypes.func.isRequired,
};

export default TicketsSortedByControl;
