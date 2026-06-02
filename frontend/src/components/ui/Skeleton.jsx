export function CarCardSkeleton() {
  return (
    <div className="overflow-hidden rounded-xl bg-white shadow-card">
      <div className="skeleton aspect-[16/10]" />
      <div className="space-y-3 p-5">
        <div className="skeleton h-5 w-3/4 rounded" />
        <div className="skeleton h-4 w-1/2 rounded" />
        <div className="flex gap-2">
          <div className="skeleton h-6 w-16 rounded-full" />
          <div className="skeleton h-6 w-16 rounded-full" />
        </div>
        <div className="skeleton h-8 w-1/3 rounded" />
        <div className="flex gap-2 pt-2">
          <div className="skeleton h-10 flex-1 rounded-lg" />
          <div className="skeleton h-10 flex-1 rounded-lg" />
        </div>
      </div>
    </div>
  )
}

export function CarGridSkeleton({ count = 6 }) {
  return (
    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      {Array.from({ length: count }).map((_, i) => (
        <CarCardSkeleton key={i} />
      ))}
    </div>
  )
}
