const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, TextControl, Notice } = wp.components;

// Register the block
registerBlockType('retroeh/game-display', {
    title: __('RetroEH Game Display', 'retroeh'),
    description: __('Display game details from RetroAchievements.', 'retroeh'),
    icon: 'smiley',
    category: 'widgets',
    attributes: {
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
        const { username, game_id } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title={__('RetroEH Settings', 'retroeh')} initialOpen={true}>
                        <Notice status="info" isDismissible={false}>
                            {__('Set your RetroAchievements API key under Settings → RetroEh! to keep it secure.', 'retroeh')}
                        </Notice>
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