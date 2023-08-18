import * as React from "react";

interface TranslationStepsIndicatorProps {
    steps: 2|3;
    mark: number;
}

export const StepsIndicator = (props:TranslationStepsIndicatorProps) => {
    let viewBoxWidth = props.steps > 2 ? 205 : 130;
    return (
        <svg viewBox={"0 0 " + viewBoxWidth + " 48.25"}>
            <defs>
                <style>{`
            .cls-1{fill:#fff;}
            .cls-1,.cls-2{stroke:#5080be;stroke-miterlimit:10;stroke-width:1.87px;}
            .cls-2{fill:none;}
            .cls-3{opacity:0.86;fill:url(#linear-gradient);}
        `}
                </style>
                <linearGradient id="linear-gradient" x1="23.19" y1="47.31" x2="23.19" y2="0.93" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stopColor="#6468ac"/>
                    <stop offset="1" stopColor="#326cb1"/>
                </linearGradient>
            </defs>
            <title></title>
            <g id="Layer_2" data-name="Layer 2">
                <g id="Layer_1-2" data-name="Layer 1">
                    <circle className={ props.mark > 0 ? 'cls-3' : 'cls-2' } cx="24" cy="24.12" r="23.19"/>
                    <line className="cls-1" x1="46.38" y1="24.12" x2="78.36" y2="24.12"/>
                    <circle className={ props.mark > 1 ? 'cls-3' : 'cls-2' } cx="101.55" cy="24.12" r="23.19"/>
                    {props.steps > 2 ? <line className="cls-1" x1="124.74" y1="24.12" x2="156.72" y2="24.12"/> : ''}
                    {props.steps > 2 ? <circle className={ props.mark > 2 ? 'cls-3' : 'cls-2' } cx="179.91" cy="24.12" r="23.19"/> : ''}
                </g>
            </g>
        </svg>
    )
}

