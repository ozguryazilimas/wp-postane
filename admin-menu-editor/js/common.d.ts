interface AmeDictionary<T> {
	[mapKey: string] : T;
}

// noinspection JSUnusedGlobalSymbols
type KeysMatchingType<T, V> = { [K in keyof T]: T[K] extends V ? K : never }[keyof T];