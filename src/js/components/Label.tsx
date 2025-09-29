import { forwardRef } from '@wordpress/element';
import { type ComponentProps, type ReactNode } from 'react';

type LabelProps = Omit<ComponentProps<'label'>, 'children'> & {
  children: ReactNode;
  required?: boolean;
  tooltip?: string;
};

export const Label = forwardRef<HTMLLabelElement, LabelProps>(
  ({ htmlFor, children, required, tooltip, ...props }, ref) => {
    return (
      <label
        htmlFor={htmlFor}
        className="block font-medium text-sm"
        {...props}
        ref={ref}
      >
        <span>{children}</span>{' '}
        {required ? <span className="text-red-500">*</span> : ''}
      </label>
    );
  },
);
Label.displayName = 'Label';
