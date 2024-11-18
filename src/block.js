const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl } = wp.components;

// Register the block
registerBlockType('retroeh/game-display', {
    title: __('RetroEH Game Display', 'retroeh'),
    description: __('Display game details from RetroAchievements.', 'retroeh'),
    icon: 'smiley',
    category: 'widgets',
    attributes: {
        api_key: {
            type: 'string',
            default: '',
        },
        username: {
            type: 'string',
            default: '',
        },
        game_id: {
            type: 'string',
            default: '',
        },
    },
    edit({ attributes, setAttributes }) {
        const { api_key, username, game_id } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('RetroEH Settings', 'retroeh')} initialOpen={true}>
                        <TextControl
                            label={__('API Key', 'retroeh')}
                            value={api_key}
                            onChange={(value) => setAttributes({ api_key: value })}
                            placeholder={__('Enter your API key', 'retroeh')}
                        />
                        <TextControl
                            label={__('Username', 'retroeh')}
                            value={username}
                            onChange={(value) => setAttributes({ username: value })}
                            placeholder={__('Enter a username', 'retroeh')}
                        />
                        <TextControl
                            label={__('Game ID (optional)', 'retroeh')}
                            value={game_id}
                            onChange={(value) => setAttributes({ game_id: value })}
                            placeholder={__('Enter a game ID', 'retroeh')}
                        />
                    </PanelBody>
                </InspectorControls>
                <div className="retroeh-block-placeholder">
                    {game_id
                        ? __('Displaying game by Game ID: ', 'retroeh') + game_id
                        : __('Displaying the latest game played by: ', 'retroeh') + username}
                </div>
            </>
        );
    },
    save() {
        // This block is dynamic; content is rendered on the server.
        return null;
    },
});