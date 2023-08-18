import * as React from "react";
import {ValidationButton} from "./ValidationButton";

interface Props {
}

export const ValidationInstructions = (props:Props) => {
    return <div className='validation-instructions'>
        <div className='validation-instructions__line'><ValidationButton vote={-1} /><div>The translation is not correct</div></div>
        <div className='validation-instructions__line'><ValidationButton vote={1} /><div>The translation is understandable but it has errors</div></div>
        <div className='validation-instructions__line'><ValidationButton vote={2} /><div>It's a good translation with one or two grammar or spelling mistakes</div></div>
        <div className='validation-instructions__line'><ValidationButton vote={3} /><div>It's a great translation</div></div>
    </div>
}