// External Dependencies
import React, { Component } from 'react';

/**
 * What Divi 4 has to say for itself, and nothing more.
 *
 * The module's settings are the record of how the module is embedded - the shortcode holds the layout, the
 * modal triggers, the offsets and the depth - and `meta` holds what the card shows about the module. Every
 * action on the card is the shared one (CONTRACT B6).
 */
export const SLUG = 'divi4';

const metaOf = (settings) => {
    try {
        return settings && settings.meta ? JSON.parse(settings.meta) || {} : {};
    } catch (error) {
        return {};
    }
};

//Written as 'on'/'off' before this, and read as 'yes'/'no' by the card that displayed it - which is why the
//premium and unregistered labels never appeared on a Divi 4 module. Both spellings are accepted here.
const flag = (value) => value === 'yes' || value === 'on' || value === true;

export const adapter = {
    read: (ctx) => {
        const settings = ctx.settings || {};
        const meta = metaOf(settings);
        const model = window.SR7.Block.normalize(window.SR7.Block.fromShortcode(settings.shortcode || ''));

        if (settings.alias) model.alias = settings.alias;
        model.moduleId = meta.moduleId;
        model.title = meta.title;
        model.type = meta.type;
        model.slides = meta.slides;
        model.cover = { image: meta.image, color: meta.color };
        model.premium = flag(meta.premium);
        model.notFound = flag(meta.notFound);
        //Saved before the grammar gained wrapperid (B3). The shortcode wins wherever it has one.
        if (!model.wrapperid) model.wrapperid = settings.wrapperid || '';

        return model;
    },

    write: (ctx, model) => {
        if (!ctx.onChange) return;
        const registered = window.SR7.E && window.SR7.E.registered;

        ctx.onChange('shortcode', window.SR7.Block.toShortcode(model));
        ctx.onChange('alias', model.alias || '');
        ctx.onChange('meta', JSON.stringify(Object.assign(metaOf(ctx.settings), {
            alias: model.alias || '',
            title: model.title || '',
            slides: model.slides || '',
            type: model.type || '',
            moduleId: model.moduleId || '',
            image: (model.cover && model.cover.image) || '',
            color: (model.cover && model.cover.color) || '',
            premium: model.premium ? 'yes' : 'no',
            registered: registered ? 'yes' : 'no',
            notFound: model.notFound ? 'yes' : 'no'
        })));
        //dropped only once the shortcode can hold it: toParams() gates the layout attributes on registration
        ctx.onChange('wrapperid', registered ? '' : ((ctx.settings || {}).wrapperid || ''));
    },

    //Quick Edit is scoped to the post being edited, which Divi keeps on its own builder object
    postId: () => (window.ETBuilderBackend && window.ETBuilderBackend.postId) || (window.SR7.E && window.SR7.E.post_id) || ''
};

/**
 * What the card is currently showing, so it is only redrawn when that changes.
 */
export const cardStamp = (settings) => {
    const meta = metaOf(settings);
    return [settings.alias, settings.shortcode, settings.wrapperid,
        meta.title, meta.slides, meta.type, meta.image, meta.color, meta.premium, meta.notFound].join('|');
};

/**
 * The shared card, mounted into Divi 4's React tree.
 *
 * SR7.Block.card returns an element rather than markup, so it is hosted through a ref instead of being
 * owned here - the same way the Divi 5 module mounts it (CONTRACT B4).
 */
export class Sr7Card extends Component {

    constructor(props) {
        super(props);
        this.host = React.createRef();
    }

    draw() {
        const tpt = window._tpt;
        if (!this.host.current || !tpt || 'function' !== typeof tpt.regResource) return;

        tpt.regResource({ id: 'tools_shortcode', url: window.SR7.E.plugin_url + 'admin/assets/js/tools/shortcode.js' });
        tpt.checkResources(['tools_shortcode']).then(() => {
            const host = this.host.current;
            if (!host) return;

            window.SR7.B.shortcode.fixAjaxUrl();
            window.SR7.Builders.register(SLUG, adapter);
            host.innerHTML = '';
            host.append(window.SR7.Builders.card(SLUG, this.props.ctx, {
                variant: this.props.variant,
                actions: this.props.actions
            }));
        });
    }

    componentDidMount() {
        this.draw();
    }

    componentDidUpdate(prevProps) {
        if (prevProps.stamp !== this.props.stamp) this.draw();
    }

    render() {
        return <div className={'sr--divi4--card sr--divi4--card--' + this.props.variant} ref={this.host} />;
    }
}
