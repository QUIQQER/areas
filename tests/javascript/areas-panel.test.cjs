const assert = require('node:assert/strict');
const {readFileSync} = require('node:fs');
const {resolve} = require('node:path');
const {test} = require('node:test');
const vm = require('node:vm');

function fixture() {
    let definition;
    const calls = [];
    const element = () => ({
        style: {removeProperty() {}},
        attributes: {},
        firstElementChild: {setAttribute() {}},
        appendChild() {},
        setAttribute(name, value) { this.attributes[name] = value; }
    });

    class Button {
        constructor(options) { this.options = options; this.element = element(); }
        getAttribute(name) { return this.options[name]; }
        getElm() { return this.element; }
        enable() { this.options.disabled = false; }
        disable() { this.options.disabled = true; }
    }

    class Grid {
        constructor(container, options) {
            this.options = options;
            this.buttons = Array.from(options.buttons, options => new Button(options));
            this.selected = [];
        }
        getButton(name) { return this.buttons.find(Button => Button.getAttribute('name') === name); }
        addEvents(events) { this.events = events; }
        refresh() {}
        getSelectedData() { return this.selected; }
        setData(data) { this.data = data; this.selected = []; }
    }

    const dependencies = {
        'controls/grid/Grid': Grid,
        'package/quiqqer/areas/bin/classes/Handler': function() {
            this.getList = () => Promise.resolve({total: 1, data: [{id: 4, countries: 'DE'}]});
        },
        Locale: {get: (group, key) => key}
    };
    vm.runInNewContext(readFileSync(resolve(__dirname, '../../bin/controls/Areas.js'), 'utf8'), {
        Class: function(options) { return options; },
        document: {createElement: element},
        define(name, names, factory) {
            definition = factory(...names.map(name => dependencies[name] ?? {}));
        }
    });
    const panel = Object.assign({}, definition, {
        getContent: element,
        addButton() { throw new Error('Area actions must be in the grid toolbar'); },
        getButtons() { throw new Error('Area actions must be accessed through the grid'); },
        Loader: {show() {}, hide() {}},
        createChild: () => calls.push(['create']),
        deleteChildren: ids => calls.push(['delete', Array.from(ids)])
    });
    panel.$onCreate();
    return {panel, calls};
}

test('area actions use descriptive labels in the grid with accessible deletion on the right', () => {
    const {panel} = fixture();
    const grid = panel.$Grid;
    assert.deepEqual(grid.buttons.map(Button => Button.getAttribute('name')), ['add', 'delete']);
    assert.equal(grid.getButton('add').getAttribute('text'), 'window.create.title');
    const Delete = grid.getButton('delete');
    assert.equal(Delete.getAttribute('position'), 'right');
    assert.equal(Delete.getAttribute('title'), 'window.delete.title');
    assert.equal(Delete.getElm().attributes['aria-label'], 'window.delete.title');
    assert.equal(Delete.getAttribute('disabled'), true);
});

test('area deletion supports multiple selected rows and ignores an empty selection', () => {
    const {panel, calls} = fixture();
    const grid = panel.$Grid;
    const Delete = grid.getButton('delete');
    Delete.options.events.onClick();
    assert.deepEqual(calls, []);
    grid.selected = [{id: 4}, {id: 7}];
    grid.events.onClick();
    assert.equal(Delete.getAttribute('disabled'), false);
    Delete.options.events.onClick();
    grid.getButton('add').options.events.onClick();
    assert.deepEqual(calls, [['delete', [4, 7]], ['create']]);
    grid.selected = [];
    grid.events.onClick();
    assert.equal(Delete.getAttribute('disabled'), true);
});

test('refresh resets deletion through the grid without disabling creation', async () => {
    const {panel} = fixture();
    panel.$Grid.getButton('delete').enable();
    await panel.refresh();
    assert.equal(panel.$Grid.getButton('delete').getAttribute('disabled'), true);
    assert.equal(Boolean(panel.$Grid.getButton('add').getAttribute('disabled')), false);
    assert.equal(panel.$Grid.data.total, 1);
});
