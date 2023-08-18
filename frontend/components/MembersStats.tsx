import * as React from "react";

interface Props {
    time: string;
    amount: number;
}

export const MembersStats = (props:Props) =>
{
    const maxSize = 128;
    let size = maxSize;

    const numberOfCharacters = props.amount.toString().length;

    switch (numberOfCharacters) {
        case 0:
        case 1:
        case 2:
            size = maxSize;
            break;
        case 3:
            size = maxSize / 1.5;
            break;
        case 4:
            size = maxSize / 2;
            break;
        case 5:
            size = maxSize / 3;
            break;
        default:
            size = 12;
    }


    return <div className='home-stats members-stats'>
        <h3>New members</h3>
        <h4>{props.time}</h4>
        <hr />
        <div style={{height: 165, position: 'relative'}}>
        <div className='members-stats__amount' style={{fontSize: size}}>
            {props.amount > 0 ? props.amount : ''}
        </div>
        </div>
    </div>
}