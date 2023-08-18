import * as React from "react";

interface Props {
    time: string;
    vote1: number;
    vote2: number;
    vote3: number;
}

export const SentenceStats = (props:Props) =>
{
    let maxVotes = Math.max(props.vote1, props.vote2, props.vote3, 1);
    let maxGraph = maxVotes * 1.3;

    let relative1 = 100 * (maxGraph - props.vote1) / maxGraph;
    let relative2 = 100 * (maxGraph - props.vote2) / maxGraph;
    let relative3 = 100 * (maxGraph - props.vote3) / maxGraph;

    return <div className='home-stats sentence-stats'>
        <h3>Verified Sentences</h3>
        <h4>{props.time}</h4>
        <hr />
        <div className='bar-container'>
            <div className='bar-and-number'>
                <div className='bar'>
                    <div className='bar-cover' style={{height: relative1 + '%'}}></div>
                    <div className='bar-marker vote1'></div>
                </div>
                <div>{props.vote1}</div>
            </div>
            <div className='bar-and-number'>
                <div className='bar'>
                    <div className='bar-cover' style={{height: relative2 + '%'}}></div>
                    <div className='bar-marker vote2'></div>
                </div>
                <div>{props.vote2}</div>
            </div>
            <div className='bar-and-number'>
                <div className='bar'>
                    <div className='bar-cover' style={{height: relative3 + '%'}}></div>
                    <div className='bar-marker vote3'></div>
                </div>
                <div>{props.vote3}</div>
            </div>

        </div>


    </div>
}