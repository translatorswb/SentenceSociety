import * as React from "react";

interface Props {
    translations: number;
}

export const ProjectTarget = (props:Props) => {

    let goal = props.translations >= (0.2 * 1000000) ?
        <text className="cls-10" transform="translate(24.69 68.68)">{Math.floor(props.translations / 10000)} %
            <tspan x="-5.16" y="30">of our</tspan>
            <tspan x="2.81" y="60">goal</tspan>
        </text>
        :
        <text className="cls-10" transform="translate(31.64 81.77)">Our <tspan className="cls-11" x="-4.15"
                                                                               y="30">goal</tspan>
        </text>;

    return <svg viewBox="-1 -1 672.61 178.53">
        <defs>
            <style>{`
                .cls-1, .cls-12 {
                fill: #fff;
            }

                .cls-2 {
                fill: url(#linear-gradient);
            }

                .cls-3 {
                fill: url(#linear-gradient-2);
            }

                .cls-4 {
                fill: url(#linear-gradient-3);
            }

                .cls-5 {
                fill: url(#linear-gradient-4);
            }

                .cls-6 {
                fill: url(#linear-gradient-5);
            }

                .cls-7 {
                fill: url(#linear-gradient-6);
            }

                .cls-8 {
                fill: url(#linear-gradient-7);
            }

                .cls-9 {
                fill: url(#linear-gradient-8);
            }

                .cls-10 {
                font-size: 25px;
                font-family: urw-din;
            }

                .cls-10, .cls-13 {
                fill: #8160a5;
            }

                .cls-11 {
                letter - spacing: 0em;
            }

                .cls-12 {
                stroke - miterlimit: 10;
                stroke: url(#linear-gradient-9);
            }

                .cls-13 {
                font-size: 23.87px;
                font-family: urw-din;
                font-weight: 500;
            }

                .cls-14 {
                letter - spacing: 0em;
            }

                .cls-15 {
                letter - spacing: 0.01em;
            }

                .cls-16 {
                letter - spacing: 0.02em;
            }

                .cls-17 {
                letter - spacing: 0.01em;
            }

                .cls-18 {
                letter - spacing: 0.02em;
            }

                .cls-19 {
                letter - spacing: 0.01em;
            }

                .cls-20 {
                letter - spacing: 0.02em;
            }

                .cls-21 {
                fill: url(#linear-gradient-10);
            }

                .cls-22 {
                fill: url(#linear-gradient-11);
            }`}
            </style>
            <linearGradient id="linear-gradient" y1="88.78" x2="671.61" y2="88.78" gradientUnits="userSpaceOnUse">
                <stop offset="0" stopColor="#825fa3"/>
                <stop offset="0.21" stopColor="#7e5fa3"/>
                <stop offset="0.43" stopColor="#7161a5"/>
                <stop offset="0.66" stopColor="#5b63a7"/>
                <stop offset="0.89" stopColor="#3e65aa"/>
                <stop offset="1" stopColor="#2d67ac"/>
            </linearGradient>
            <linearGradient id="linear-gradient-2" x1="165.01" y1="112.57" x2="165.01" y2="32.6"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-3" x1="263.48" y1="113.58" x2="263.48" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-4" x1="323.87" y1="113.58" x2="323.87" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-5" x1="384.26" y1="113.58" x2="384.26" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-6" x1="476.19" y1="113.58" x2="476.19" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-7" x1="536.58" y1="113.58" x2="536.58" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-8" x1="596.97" y1="113.58" x2="596.97" y2="31.59"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-9" x1="102.02" y1="88.77" x2="103.02" y2="88.77"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-10" x1="215.65" y1="118.79" x2="215.65" y2="104.2"
                            href="#linear-gradient"/>
            <linearGradient id="linear-gradient-11" x1="430.99" y1="118.79" x2="430.99" y2="104.2"
                            href="#linear-gradient"/>
        </defs>
        <title></title>
        <g id="Layer_2" data-name="Layer 2">
            <g id="Layer_2-2" data-name="Layer 2">
                <g>
                    <g>
                        <g>
                            <rect className="cls-1" y="0.02" width="671.61" height="177.51" rx="31.99"/>
                            <path className="cls-2"
                                  d="M639.62,1a31,31,0,0,1,31,31V145.54a31,31,0,0,1-31,31H32a31,31,0,0,1-31-31V32A31,31,0,0,1,32,1H639.62m0-1H32A32,32,0,0,0,0,32V145.54a32,32,0,0,0,32,32H639.62a32,32,0,0,0,32-32V32a32,32,0,0,0-32-32Z"/>
                        </g>
                        <g>
                            <path className="cls-3" d="M168.7,42.46l-13.76,6L152,41.11l19.57-8.51H178v80H168.7Z"/>
                            <path className="cls-4"
                                  d="M241.06,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.5-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.46-9.08-2.68-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29s-13.31,6.39-13.31,22.29V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                            <path className="cls-5"
                                  d="M301.45,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.51-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.45-9.08-2.69-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29s-13.31,6.39-13.31,22.29V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                            <path className="cls-6"
                                  d="M361.84,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.51-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.45-9.08-2.69-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29S371,45.82,371,61.72V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                            <path className="cls-7"
                                  d="M453.77,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.5-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.46-9.08-2.68-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29s-13.31,6.39-13.31,22.29V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                            <path className="cls-8"
                                  d="M514.16,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.51-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.45-9.08-2.69-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29s-13.31,6.39-13.31,22.29V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                            <path className="cls-9"
                                  d="M574.55,60.82c0-10.52,1.45-17,4.92-21.5,4.14-5.38,9.51-7.73,17.56-7.73,8.5,0,14.31,2.91,18.34,9.07,2.68,4.26,4,10.64,4,20.16V84.34c0,10.53-1.45,17-4.92,21.51-4.13,5.37-9.5,7.73-17.44,7.73-8.61,0-14.43-2.92-18.45-9.08-2.69-4.25-4-10.64-4-20.16Zm35.67.9c0-15.9-3.8-22.29-13.19-22.29s-13.31,6.39-13.31,22.29V83.45c0,15.9,3.8,22.29,13.31,22.29s13.19-6.39,13.19-22.29Z"/>
                        </g>
                        {goal}
                        <line className="cls-12" x1="102.52" y1="177.53" x2="102.52"/>
                        <text className="cls-13" transform="translate(233.81 150.07)">sentences for understanding
                        </text>
                    </g>
                    <path className="cls-21" d="M213,104.2h9.29l-5.76,14.59H209Z"/>
                    <path className="cls-22" d="M428.32,104.2h9.29l-5.76,14.59h-7.47Z"/>
                </g>
            </g>
        </g>
    </svg>
}