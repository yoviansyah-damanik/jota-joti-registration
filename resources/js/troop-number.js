// Format nomor gudep xx-xxx; nomor kedua = nomor pertama (ganjil) + 1
export function formatTroopNumber(value) {
    const digits = String(value ?? '').replace(/\D/g, '').slice(0, 5);

    return digits.length > 2 ? `${digits.slice(0, 2)}-${digits.slice(2)}` : digits;
}

export function pairTroopNumber(first) {
    if (!/^\d{2}-\d{3}$/.test(first ?? '')) return '';

    const last = parseInt(first.slice(3), 10);
    if (last % 2 === 0 || last >= 999) return '';

    return `${first.slice(0, 3)}${String(last + 1).padStart(3, '0')}`;
}

export default (model) => ({
    first: model,

    init() {
        this.first = formatTroopNumber(this.first);
    },

    update(input) {
        this.first = formatTroopNumber(input.value);
        input.value = this.first;
    },

    get pair() {
        return pairTroopNumber(this.first);
    },

    get isEven() {
        return /^\d{2}-\d{3}$/.test(this.first ?? '') && parseInt(this.first.slice(3), 10) % 2 === 0;
    },
});
