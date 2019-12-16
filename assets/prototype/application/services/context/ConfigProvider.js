/**
 * External imports
 */
import { createContext } from '@wordpress/element';
import { pick, propOr } from 'ramda';
import { useQuery } from '@apollo/react-hooks';

import { GET_GENERAL_SETTINGS } from '../../../domain/eventEditor/data/queries/generalSettings/generalSettings';
import { GET_CURRENT_USER } from '../../../domain/eventEditor/data/queries/currentUser/currentUser';

export const ConfigContext = createContext();

const ConfigProvider = ({ children }) => {
	const { data: generalSettingsData, error: generalSettingsError, loading: generalSettingsLoading } = useQuery(
		GET_GENERAL_SETTINGS
	);

	const { data: currentUserData, error: currentUserError, loading: currentUserLoading } = useQuery(GET_CURRENT_USER);

	/**
	 * To be updated (if we need it) according to this implementation https://github.com/eventespresso/event-espresso-core/pull/1974
	 */
	console.log('ConfigProvider', generalSettingsError, generalSettingsLoading, currentUserError, currentUserLoading);

	const generalSettings = propOr({}, 'generalSettings', generalSettingsData);
	const currentUser = propOr({}, 'viewer', currentUserData);
	const { dateFormat, timeFormat, timezone } = pick(['dateFormat', 'timeFormat', 'timezone'], generalSettings);

	const currentUserProps = pick(
		[
			'description',
			'email',
			'firstName',
			'id',
			'name',
			'nicename',
			'nickname',
			'lastName',
			'locale',
			'userId',
			'username',
		],
		currentUser
	);
	const value = { dateFormat, timeFormat, timezone, ...currentUserProps };

	return <ConfigContext.Provider value={value}>{children}</ConfigContext.Provider>;
};

export default ConfigProvider;
