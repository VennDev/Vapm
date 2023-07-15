# Vapm
- A library for PHP about Async, Promise and other non-blocking methods.
- The method is based on Fibers, requires you to have php version from >= 8.1

# Clarify
- This is a fiber-based library of php that helps your code become asynchronous, and uses asynchronous methods such as fetch, read, ... help for the use of reading or retrieving results from the website in a non-blocking manner.
- As explained by PHP:

Fibers represent full-stack, interruptible functions. Fibers may be suspended from anywhere in the call-stack, pausing execution within the fiber until the fiber is resumed at a later time.

Fibers pause the entire execution stack, so the direct caller of the function does not need to change how it invokes the function.

Execution may be interrupted anywhere in the call stack using Fiber::suspend() (that is, the call to Fiber::suspend() may be in a deeply nested function or not even exist at all).

Unlike stack-less Generators, each Fiber has its own call stack, allowing them to be paused within deeply nested function calls. A function declaring an interruption point (that is, calling Fiber::suspend()) need not change its return type, unlike a function using yield which must return a Generator instance.

Fibers can be suspended in any function call, including those called from within the PHP VM, such as functions provided to array_map() or methods called by foreach on an Iterator object.

Once suspended, execution of the fiber may be resumed with any value using Fiber::resume() or by throwing an exception into the fiber using Fiber::throw(). The value is returned (or exception thrown) from Fiber::suspend().

- Shows are: Shows that the pause and continuation of Fiber is very effective. Because each Fiber has its own call stack.
- It's clear that fibers are a significant improvement, both syntax-wise and in flexibility.

# Why is it called unblocking? 
- With this library, Fiber will take care of everything, thereby helping to check if the main thread is still working and stop promises or tasks in the EventLoop queue.

# Next update?
- Simply add some other asynchronous features so that this library is as similar to Javascript as possible.
- If you have any features you'd like to contribute or have any ideas, please give me feedback. I will always update this project in the near future.

# How to use my lib?
- [Click Here](https://venndev.gitbook.io/vapm/)
