'use strict';

namespace AmeMiniFunc {
	interface OptionOps<T> {
		isDefined(): this is Some<T>;

		isEmpty(): this is None;

		nonEmpty(): this is Some<T>;

		get(): T;

		map<R>(f: (value: T) => R): Option<R>;

		flatMap<R>(f: (value: T) => Option<R>): Option<R>;

		filter(f: (value: T) => boolean): Option<T>;

		forEach(f: (value: T) => void): void;

		orElse(alternative: () => Option<T>): Option<T>;

		getOrElse(defaultValue: () => T): T;

		orNull<R extends T>(): T | R | null;

		toArray(): T[];
	}

	class Some<T> implements OptionOps<T> {
		constructor(private value: T) {
		}

		get(): T {
			return this.value;
		}

		isDefined(): boolean {
			return true;
		}

		isEmpty(): boolean {
			return false;
		}

		nonEmpty(): boolean {
			return true;
		}

		map<R>(f: (value: T) => R): Some<R> {
			return new Some(f(this.value));
		}

		flatMap<R>(f: (value: T) => Option<R>): Option<R> {
			return f(this.value);
		}

		filter(f: (value: T) => boolean): Option<T> {
			return f(this.value) ? this : none;
		}

		forEach(f: (value: T) => void): void {
			f(this.value);
		}

		orElse(alternative: () => Option<T>): Option<T> {
			return this;
		}

		getOrElse(alternative: () => T): T {
			return this.value;
		}

		toArray(): T[] {
			return [this.value];
		}

		orNull(): T | null {
			return this.value;
		}
	}

	class None implements OptionOps<never> {
		map<R>(f: (value: never) => R): None {
			return this;
		}

		get(): never {
			throw new Error('Cannot get value from None');
		}

		isDefined(): boolean {
			return false;
		}

		isEmpty(): boolean {
			return true;
		}

		nonEmpty(): boolean {
			return false;
		}

		filter(f: (value: never) => boolean): None {
			return this;
		}

		forEach(f: (value: never) => void): void {
		}

		orElse<R>(alternative: () => Option<R>): Option<R> {
			return alternative();
		}

		getOrElse<R>(alternative: () => R): R {
			return alternative();
		}

		orNull(): null {
			return null;
		}

		flatMap<R>(f: (value: never) => Option<R>): Option<R> {
			return this;
		}

		toArray(): [] {
			return [];
		}
	}

	export const none = new None();

	export function some<T>(value: T): Some<T> {
		return new Some(value);
	}

	export type Option<T> = Some<T> | None;

	type LiftedFunction<TArgs extends any[], R> = (
		...args: { [K in keyof TArgs]: TArgs[K] extends Option<infer U> ? U : never }
	) => R;

	export function lift<TArgs extends Option<any>[], R>(
		options: [...TArgs],
		f: LiftedFunction<TArgs, R>
	): Option<R> {
		const areAllDefined = options.every((opt) => opt.isDefined());

		if (areAllDefined) {
			const unwrappedValues = options.map((opt) => opt.get()) as unknown as Parameters<LiftedFunction<TArgs, R>>;
			return some(f(...unwrappedValues));
		} else {
			return none;
		}
	}
}