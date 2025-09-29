import type { ReactNode } from 'react';

export type LinkProps = {
  to: string;
  children: ReactNode;
  className?: string;
  target?: string;
  rel?: string;
  ref?: React.Ref<HTMLAnchorElement>;
};

export const Link = ({ to, children, className, target, rel }: LinkProps) => (
  <a href={to} className={className} target={target} rel={rel}>
    {children}
  </a>
);
