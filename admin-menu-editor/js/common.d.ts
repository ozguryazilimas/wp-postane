///<reference path="knockout.d.ts"/>

interface AmeDictionary<T> {
	[mapKey: string]: T;
}

// noinspection JSUnusedGlobalSymbols
type KeysMatchingType<T, V> = { [K in keyof T]: T[K] extends V ? K : never }[keyof T];

type AmeCssBorderStyle = 'none' | 'solid' | 'dashed' | 'dotted' | 'double' | 'groove' | 'ridge' | 'outset';

interface AmeCssBorderSettings {
	style: AmeCssBorderStyle;
	color: string;
	width: number;
}

type AmeObservablePropertiesOf<T> = {
	[P in keyof T]: KnockoutObservable<T[P]>;
}

type AmeRecursiveObservablePropertiesOf<T> = {
	[P in keyof T]: T[P] extends object ? AmeRecursiveObservablePropertiesOf<T[P]> : KnockoutObservable<T[P]>;
}

type Constructor<T> = new (...args: any[]) => T;

interface JQuery {
	//My jQuery typings are out of date, and are missing this signature for the "off" method
	//where the callback takes additional arguments.
	off(events: string, handler: (eventObject: JQueryEventObject, ...args: any[]) => any): JQuery;
}

/**
 * Partial type definition for the WordPress "wp" global.
 * Sure would be nice if WordPress provided this.
 */
declare const wp: {
	codeEditor: {
		initialize: (textarea: string, options: Partial<WpEditorInitSettings>) => any;
	};
	media: {
		(attributes: {
			button: { text: string };
			library: { type: string };
			multiple: boolean;
			title: string
		}): any;

		attachment(id: number): any;
	};
	editor: {
		remove: (id: string) => void;
		initialize: (id: string, settings: object) => any;
	};
};

/**
 * Incomplete type definition for the settings object that can be passed to wp.editor.initialize().
 */
interface WpEditorInitSettings {
	tinymce: boolean | {
		wpautop: boolean;
		toolbar1?: string,
		toolbar2?: string,
		toolbar3?: string,
		toolbar4?: string,
	};
	quicktags: boolean | {
		buttons?: string;
	};
	mediaButtons: boolean;
}

/**
 * Webpack will replace this with a boolean literal. Note that
 * when not using Webpack, it will be undefined, so a typeof check
 * is recommended.
 */
declare const AME_IS_PRODUCTION: boolean;