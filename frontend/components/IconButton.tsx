import * as React from "react";
import classNames from "classnames";
import {Component} from "react";

interface Props {
    type: string,
    clicked?: () => void,
    clickedOutside?: () => void,
    help?: React.ReactNode,
    disabled?: boolean,
    keepOpen?: boolean,
}
interface State {
    tooltip: boolean
}
export class IconButton extends Component<Props, State> {
    private wrapperRef:React.RefObject<HTMLDivElement>;

    constructor(props:Props) {
        super(props);
        this.state = {
            tooltip: this.props.keepOpen || false,
        }
        this.handleClick = this.handleClick.bind(this);
        this.wrapperRef = React.createRef<HTMLDivElement>();
    }
    componentWillMount() {
        if (this.props.clickedOutside) {
            document.addEventListener('mousedown', this.handleClick, false)
            document.addEventListener('touchstart', this.handleClick, false)
        }
    }
    componentWillUnmount() {
        document.removeEventListener('mousedown', this.handleClick, false)
        document.removeEventListener('touchstart', this.handleClick, false)
    }
    componentDidUpdate(prevProps:Props) {
        if (this.props.keepOpen !== prevProps.keepOpen) {
            this.showToolTip(this.props.keepOpen || false);
        }
    }
    handleClick(e:any) {
        if (this.props.clickedOutside) {
            if (this.wrapperRef.current && !this.wrapperRef.current.contains(e.target)) {
                this.props.clickedOutside();
            }
        }
    }
    showToolTip(value:boolean) {
        this.setState({tooltip: this.props.keepOpen || value})
    }
    renderHelp() {
        return  <div className='icon-button--tooltip'>
            {this.props.help}
        </div>;
    }
    render() {
        return (
            <div
                className={'icon-button-wrap ' + this.props.type + '-wrap'}
                onMouseEnter={() => {!this.props.disabled && this.showToolTip(true);}}
                onMouseLeave={() => {this.showToolTip(false);}}
                ref={this.wrapperRef}
            >
                <button
                    className={classNames({['icon-button ' + this.props.type]: true})}
                    onClick={() => {this.props.clicked && this.props.clicked()}}
                    disabled={this.props.disabled}
                ></button>
                {this.state.tooltip ? this.renderHelp() : ''}
            </div>
        )
    }
}
