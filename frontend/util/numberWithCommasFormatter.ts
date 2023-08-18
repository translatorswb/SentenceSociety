export default function numberWithCommas(val:number) {
    // thanks https://stackoverflow.com/a/2901298/31884
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}