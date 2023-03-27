'use strict';
var AmeMiniFunc;
(function (AmeMiniFunc) {
    class Some {
        constructor(value) {
            this.value = value;
        }
        get() {
            return this.value;
        }
        isDefined() {
            return true;
        }
        isEmpty() {
            return false;
        }
        nonEmpty() {
            return true;
        }
        map(f) {
            return new Some(f(this.value));
        }
        flatMap(f) {
            return f(this.value);
        }
        filter(f) {
            return f(this.value) ? this : AmeMiniFunc.none;
        }
        forEach(f) {
            f(this.value);
        }
        orElse(alternative) {
            return this;
        }
        getOrElse(alternative) {
            return this.value;
        }
        toArray() {
            return [this.value];
        }
        orNull() {
            return this.value;
        }
    }
    class None {
        map(f) {
            return this;
        }
        get() {
            throw new Error('Cannot get value from None');
        }
        isDefined() {
            return false;
        }
        isEmpty() {
            return true;
        }
        nonEmpty() {
            return false;
        }
        filter(f) {
            return this;
        }
        forEach(f) {
        }
        orElse(alternative) {
            return alternative();
        }
        getOrElse(alternative) {
            return alternative();
        }
        orNull() {
            return null;
        }
        flatMap(f) {
            return this;
        }
        toArray() {
            return [];
        }
    }
    AmeMiniFunc.none = new None();
    function some(value) {
        return new Some(value);
    }
    AmeMiniFunc.some = some;
    function lift(options, f) {
        const areAllDefined = options.every((opt) => opt.isDefined());
        if (areAllDefined) {
            const unwrappedValues = options.map((opt) => opt.get());
            return some(f(...unwrappedValues));
        }
        else {
            return AmeMiniFunc.none;
        }
    }
    AmeMiniFunc.lift = lift;
})(AmeMiniFunc || (AmeMiniFunc = {}));
//# sourceMappingURL=mini-func.js.map