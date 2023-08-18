
const listener = (ev:any) =>
{
    ev.preventDefault();
    return ev.returnValue = 'Are you sure you want to close?';
}

export default (enable: boolean) => {

    // only activate for producation site
    if (process.env.NODE_ENV !== 'production') {
        return;
    }

    if (enable) {
        window.addEventListener("beforeunload", listener);
    } else {
        window.removeEventListener("beforeunload", listener)
    }
}