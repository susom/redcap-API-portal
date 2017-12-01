/*! Insert global custom js here */

/*
CUSTOM REACT COMPONENTS

ES6 
class HelloComponent extends React.Component {  
  render() {
    return <div>Hello {this.props.name}</div>;
  }
}

ES5
*/
var HelloComponent = React.createClass({  
    render: function() {
        return <div>Hello {this.props.name}</div>;
    }
});

