import React from 'react';
import NumericInput, { NumericInputProps } from 'react-numeric-input';

const sizes: { [index: number]: string } = {
  4: 'gamification-w-20',
};

const NumberInput = ({ className = '', ...props }: NumericInputProps) => {
  const width = props.size ? sizes[props.size] || '' : '';
  return (
    <div className={`gamification-inline-block ${width}`}>
      <NumericInput {...props} className={`form-control gamification-m-0 gamification-h-full ${className}`} />
    </div>
  );
};

export default NumberInput;
