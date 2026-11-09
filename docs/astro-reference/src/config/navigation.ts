export interface NavigationLink {
  kind: "link";
  label: string;
  path: string;
}

export interface NavigationGroup {
  kind: "group";
  label: string;
  children: readonly NavigationLink[];
}

export type PrimaryNavigationItem = NavigationLink | NavigationGroup;

export const primaryNavigation = [
  {
    kind: "link",
    label: "About Us",
    path: "/about-us/",
  },
  {
    kind: "link",
    label: "Our Projects",
    path: "/gallery/",
  },
  {
    kind: "group",
    label: "Our Solutions",
    children: [
      {
        kind: "link",
        label: "Workplace Charging",
        path: "/workplace-charging/",
      },
      {
        kind: "link",
        label: "Fleet Charging",
        path: "/fleet-charging-solutions/",
      },
      {
        kind: "link",
        label: "Public Charging",
        path: "/public-charging/",
      },
    ],
  },
  {
    kind: "link",
    label: "Aftercare",
    path: "/aftercare/",
  },
] as const satisfies readonly PrimaryNavigationItem[];
