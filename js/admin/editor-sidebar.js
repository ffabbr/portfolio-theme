/**
 * Fabian Theme - Block Editor Sidebar Panels
 * 
 * This script adds native Gutenberg sidebar panels for:
 * - Post subtitle field
 * - Project hero settings
 */

(function (wp) {
    const { registerPlugin } = wp.plugins;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { TextControl, TextareaControl, SelectControl, Notice } = wp.components;
    const { useSelect, useDispatch } = wp.data;
    const { useEffect, useRef, useState } = wp.element;

    /**
     * Post Subtitle Panel
     * Displayed on regular posts
     */
    const PostSubtitlePanel = () => {
        const postType = useSelect((select) => select('core/editor').getCurrentPostType());
        const postId = useSelect((select) => select('core/editor').getCurrentPostId());
        const restBase = useSelect((select) => select('core').getPostType(postType)?.rest_base) || postType;

        const meta = useSelect((select) => select('core/editor').getEditedPostAttribute('meta')) || {};
        const { editPost } = useDispatch('core/editor');

        const isSaving = useSelect((select) => select('core/editor').isSavingPost());
        const isAutosaving = useSelect((select) => select('core/editor').isAutosavingPost());
        const prevIsSaving = useRef(false);

        const [serverWarning, setServerWarning] = useState('');

        // Only show on regular posts
        if (postType !== 'post') return null;

        const updateSubtitle = (value) => {
            const nextMeta = { ...(meta || {}), _fabian_subtitle: value };
            console.debug('[fabian][subtitle] editPost meta update', { value, nextMeta });
            editPost({ meta: nextMeta });
        };

        useEffect(() => {
            const saveJustFinished = prevIsSaving.current && !isSaving && !isAutosaving;
            prevIsSaving.current = isSaving;

            if (!saveJustFinished) return;

            // Verify what the server actually persisted (if this differs, fix is PHP: register_post_meta show_in_rest)
            (async () => {
                try {
                    const record = await wp.apiFetch({ path: `/wp/v2/${restBase}/${postId}?context=edit` });
                    const serverMeta = record?.meta || {};
                    const key = '_fabian_subtitle';

                    console.debug('[fabian][subtitle] server meta after save', { serverMeta });

                    if ((meta?.[key] ?? '') !== (serverMeta?.[key] ?? '')) {
                        const msg =
                            `Saved value was not persisted by REST. ` +
                            `Edited meta["${key}"]="${meta?.[key] ?? ''}", server meta["${key}"]="${serverMeta?.[key] ?? ''}". ` +
                            `This usually means the meta key is not registered with show_in_rest (PHP).`;
                        setServerWarning(msg);
                        console.error('[fabian][subtitle]', msg, { postType, postId, restBase });
                    } else {
                        setServerWarning('');
                    }
                } catch (e) {
                    console.error('[fabian][subtitle] meta verification fetch failed', e, { postType, postId, restBase });
                }
            })();
        }, [isSaving, isAutosaving, meta, postType, postId, restBase]);

        return wp.element.createElement(
            PluginDocumentSettingPanel,
            { name: 'fabian-post-subtitle', title: 'Subtitle', icon: 'text' },
            wp.element.createElement(
                wp.element.Fragment,
                null,
                serverWarning &&
                    wp.element.createElement(Notice, { status: 'warning', isDismissible: true, onRemove: () => setServerWarning('') }, serverWarning),
                wp.element.createElement(TextControl, {
                    label: 'Subtitle',
                    hideLabelFromVision: true,
                    value: meta?._fabian_subtitle || '',
                    onChange: updateSubtitle,
                    placeholder: 'Enter a subtitle...',
                    help: 'A short subtitle displayed below the post title.',
                })
            )
        );
    };

    /**
     * Project Hero Settings Panel
     * Displayed on project post type
     */
    const ProjectHeroPanel = () => {
        const postType = useSelect((select) => select('core/editor').getCurrentPostType());
        const postId = useSelect((select) => select('core/editor').getCurrentPostId());
        const restBase = useSelect((select) => select('core').getPostType(postType)?.rest_base) || postType;

        const meta = useSelect((select) => select('core/editor').getEditedPostAttribute('meta')) || {};
        const { editPost } = useDispatch('core/editor');

        const isSaving = useSelect((select) => select('core/editor').isSavingPost());
        const isAutosaving = useSelect((select) => select('core/editor').isAutosavingPost());
        const prevIsSaving = useRef(false);

        const [serverWarning, setServerWarning] = useState('');

        const heroStyle = meta?._fabian_hero_style || 'plain';
        const heroTheme = meta?._fabian_hero_theme || 'auto';
        const subtitle = meta?._fabian_subtitle || '';
        const backgroundVideo = meta?._fabian_background_video || '';
        const annotations = meta?._fabian_annotations || '';

        // Only show on projects
        if (postType !== 'project') return null;

        const updateMeta = (key, value) => {
            console.debug('[fabian][hero] editPost meta update', { key, value });
            editPost({ meta: { [key]: value } });
        };

        useEffect(() => {
            const saveJustFinished = prevIsSaving.current && !isSaving && !isAutosaving;
            prevIsSaving.current = isSaving;

            if (!saveJustFinished) return;

            (async () => {
                try {
                    const record = await wp.apiFetch({ path: `/wp/v2/${restBase}/${postId}?context=edit` });
                    const serverMeta = record?.meta || {};

                    console.debug('[fabian][hero] server meta after save', { serverMeta });

                    const keysToCheck = [
                        '_fabian_hero_style',
                        '_fabian_hero_theme',
                        '_fabian_subtitle',
                        '_fabian_background_video',
                        '_fabian_annotations',
                    ];

                    const mismatches = keysToCheck.filter(
                        (k) => (meta?.[k] ?? '') !== (serverMeta?.[k] ?? '')
                    );

                    if (mismatches.length) {
                        const msg =
                            `Hero meta was not persisted by REST for keys: ${mismatches.join(', ')}. ` +
                            `This usually means these meta keys are not registered with show_in_rest for the "${postType}" post type (PHP).`;
                        setServerWarning(msg);
                        console.error('[fabian][hero]', msg, { mismatches, postType, postId, restBase, editedMeta: meta, serverMeta });
                    } else {
                        setServerWarning('');
                    }
                } catch (e) {
                    console.error('[fabian][hero] meta verification fetch failed', e, { postType, postId, restBase });
                }
            })();
        }, [isSaving, isAutosaving, meta, postType, postId, restBase]);

        const heroStyleOptions = [
            { label: 'Plain', value: 'plain' },
            { label: 'Annotations (Pure)', value: 'annotations' },
            { label: 'Image', value: 'image' },
            { label: 'Video', value: 'video' },
        ];

        const heroThemeOptions = [
            { label: 'Auto', value: 'auto' },
            { label: 'Light (white text)', value: 'light' },
            { label: 'Dark (dark text)', value: 'dark' },
        ];

        return wp.element.createElement(
            PluginDocumentSettingPanel,
            { name: 'fabian-project-hero', title: 'Hero Settings', icon: 'format-image' },
            wp.element.createElement(
                wp.element.Fragment,
                null,
                serverWarning &&
                    wp.element.createElement(Notice, { status: 'warning', isDismissible: true, onRemove: () => setServerWarning('') }, serverWarning),
                // Hero Style
                wp.element.createElement(SelectControl, {
                    label: 'Hero Style',
                    value: heroStyle,
                    options: heroStyleOptions,
                    onChange: (value) => updateMeta('_fabian_hero_style', value),
                    help: heroStyle === 'image' ? 'Set the Featured Image for the hero background.' : undefined,
                }),
                // Hero Theme
                wp.element.createElement(SelectControl, {
                    label: 'Hero Theme',
                    value: heroTheme,
                    options: heroThemeOptions,
                    onChange: (value) => updateMeta('_fabian_hero_theme', value),
                }),
                // Description (subtitle)
                wp.element.createElement(TextareaControl, {
                    label: 'Description',
                    value: subtitle,
                    onChange: (value) => updateMeta('_fabian_subtitle', value),
                    rows: 3,
                    help: 'A short description displayed in the hero section.',
                }),
                // Background Video URL - only show when hero style is video
                heroStyle === 'video' && wp.element.createElement(TextControl, {
                    label: 'Background Video URL',
                    value: backgroundVideo,
                    onChange: (value) => updateMeta('_fabian_background_video', value),
                    type: 'url',
                    placeholder: 'https://...',
                }),
                // Annotations - only show when hero style is annotations
                heroStyle === 'annotations' && wp.element.createElement(TextareaControl, {
                    label: 'Annotations',
                    value: annotations,
                    onChange: (value) => updateMeta('_fabian_annotations', value),
                    rows: 5,
                    help: 'Enter one word per line.',
                    style: { fontFamily: 'monospace' },
                })
            )
        );
    };

    // Register the Post Subtitle plugin
    registerPlugin('fabian-post-subtitle', {
        render: PostSubtitlePanel,
        icon: null,
    });

    // Register the Project Hero plugin
    registerPlugin('fabian-project-hero', {
        render: ProjectHeroPanel,
        icon: null,
    });

})(window.wp);
